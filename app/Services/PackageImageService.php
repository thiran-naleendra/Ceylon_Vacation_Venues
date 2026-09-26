<?php

namespace App\Services;

use App\Enums\ImageProcessingStatus;
use App\Models\PackageImage;
use App\Models\TourPackage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PackageImageService
{
    public function store(TourPackage $package, UploadedFile $upload, string $altText, int $sortOrder): PackageImage
    {
        $disk = 'public';
        $directory = 'packages/'.$package->getKey();
        $basename = (string) Str::uuid();
        $storedPaths = [];

        try {
            $large = Image::fromUpload($upload)->orient()->scale(width: 1600)->optimize('webp', 82);
            $largePath = $large->storePubliclyAs($directory, $basename.'.webp', $disk);

            if ($largePath === false) {
                throw new RuntimeException('The optimized image could not be stored.');
            }

            $storedPaths[] = $largePath;
            [$width, $height] = $large->dimensions();
            $variants = [];

            foreach ([480 => 'small', 960 => 'medium'] as $variantWidth => $label) {
                $variant = Image::fromUpload($upload)->orient()->scale(width: $variantWidth)->optimize('webp', 80);
                $variantPath = $variant->storePubliclyAs($directory, $basename.'-'.$variantWidth.'.webp', $disk);

                if ($variantPath === false) {
                    throw new RuntimeException('A responsive image variant could not be stored.');
                }

                $storedPaths[] = $variantPath;
                [$variantActualWidth, $variantActualHeight] = $variant->dimensions();
                $variants[$label] = [
                    'path' => $variantPath,
                    'width' => $variantActualWidth,
                    'height' => $variantActualHeight,
                ];
            }

            $image = $package->images()->make();
            $image->forceFill([
                'disk' => $disk,
                'path' => $largePath,
                'alt_text' => $altText,
                'mime_type' => 'image/webp',
                'file_size' => Storage::disk($disk)->size($largePath),
                'width' => $width,
                'height' => $height,
                'variants' => $variants,
                'processing_status' => ImageProcessingStatus::Ready,
                'sort_order' => $sortOrder,
            ]);
            $image->save();

            return $image;
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($storedPaths);

            throw $exception;
        }
    }

    public function deleteFiles(PackageImage $image): void
    {
        $paths = [$image->path];

        foreach ($image->variants ?? [] as $variant) {
            if (is_array($variant) && isset($variant['path'])) {
                $paths[] = $variant['path'];
            }
        }

        Storage::disk($image->disk)->delete(array_unique($paths));
    }
}
