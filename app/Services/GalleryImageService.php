<?php

namespace App\Services;

use App\Enums\ImageProcessingStatus;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GalleryImageService
{
    public function store(GalleryAlbum $album, UploadedFile $upload, array $attributes): GalleryImage
    {
        $disk = 'public';
        $directory = 'gallery/'.$album->getKey();
        $base = (string) Str::uuid();
        $paths = [];
        try {
            $large = Image::fromUpload($upload)->orient()->scale(width: 1800)->optimize('webp', 84);
            $path = $large->storePubliclyAs($directory, $base.'.webp', $disk);
            if ($path === false) {
                throw new RuntimeException('The gallery image could not be stored.');
            }
            $paths[] = $path;
            [$width, $height] = $large->dimensions();
            $variants = [];
            foreach ([480 => 'small', 960 => 'medium'] as $size => $label) {
                $variant = Image::fromUpload($upload)->orient()->scale(width: $size)->optimize('webp', 80);
                $variantPath = $variant->storePubliclyAs($directory, $base.'-'.$size.'.webp', $disk);
                if ($variantPath === false) {
                    throw new RuntimeException('A gallery image variant could not be stored.');
                }
                $paths[] = $variantPath;
                [$variantWidth, $variantHeight] = $variant->dimensions();
                $variants[$label] = ['path' => $variantPath, 'width' => $variantWidth, 'height' => $variantHeight];
            }
            $image = $album->images()->make($attributes);
            $image->forceFill(['disk' => $disk, 'path' => $path, 'mime_type' => 'image/webp', 'file_size' => Storage::disk($disk)->size($path), 'width' => $width, 'height' => $height, 'variants' => $variants, 'processing_status' => ImageProcessingStatus::Ready])->save();

            return $image;
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($paths);
            throw $exception;
        }
    }

    public function delete(GalleryImage $image): void
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
