<?php

namespace App\Services;

use App\Enums\ImageProcessingStatus;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class VehicleImageService
{
    public function store(Vehicle $vehicle, UploadedFile $upload, string $altText, int $sortOrder): VehicleImage
    {
        $disk = 'public';
        $directory = 'vehicles/'.$vehicle->getKey();
        $basename = (string) Str::uuid();
        $paths = [];
        try {
            $large = Image::fromUpload($upload)->orient()->scale(width: 1600)->optimize('webp', 82);
            $path = $large->storePubliclyAs($directory, $basename.'.webp', $disk);
            if ($path === false) {
                throw new RuntimeException('The optimized image could not be stored.');
            }
            $paths[] = $path;
            [$width, $height] = $large->dimensions();
            $variants = [];
            foreach ([480 => 'small', 960 => 'medium'] as $variantWidth => $label) {
                $variant = Image::fromUpload($upload)->orient()->scale(width: $variantWidth)->optimize('webp', 80);
                $variantPath = $variant->storePubliclyAs($directory, $basename.'-'.$variantWidth.'.webp', $disk);
                if ($variantPath === false) {
                    throw new RuntimeException('A responsive image variant could not be stored.');
                }
                $paths[] = $variantPath;
                [$variantWidthActual, $variantHeight] = $variant->dimensions();
                $variants[$label] = ['path' => $variantPath, 'width' => $variantWidthActual, 'height' => $variantHeight];
            }
            $image = $vehicle->images()->make();
            $image->forceFill(['disk' => $disk, 'path' => $path, 'alt_text' => $altText, 'mime_type' => 'image/webp', 'file_size' => Storage::disk($disk)->size($path), 'width' => $width, 'height' => $height, 'variants' => $variants, 'processing_status' => ImageProcessingStatus::Ready, 'sort_order' => $sortOrder])->save();

            return $image;
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($paths);
            throw $exception;
        }
    }

    public function deleteFiles(VehicleImage $image): void
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
