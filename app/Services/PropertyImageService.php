<?php

namespace App\Services;

use App\Enums\ImageProcessingStatus;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PropertyImageService
{
    public function store(Property $property, UploadedFile $upload, string $alt, int $order): PropertyImage
    {
        $disk = 'public';
        $dir = 'properties/'.$property->id;
        $base = (string) Str::uuid();
        $paths = [];
        try {
            $large = Image::fromUpload($upload)->orient()->scale(width: 1600)->optimize('webp', 82);
            $path = $large->storePubliclyAs($dir, $base.'.webp', $disk);
            if ($path === false) {
                throw new RuntimeException('The optimized image could not be stored.');
            }$paths[] = $path;
            [$width,$height] = $large->dimensions();
            $variants = [];
            foreach ([480 => 'small', 960 => 'medium'] as $size => $label) {
                $image = Image::fromUpload($upload)->orient()->scale(width: $size)->optimize('webp', 80);
                $variant = $image->storePubliclyAs($dir, $base.'-'.$size.'.webp', $disk);
                if ($variant === false) {
                    throw new RuntimeException('A responsive image variant could not be stored.');
                }$paths[] = $variant;
                [$w,$h] = $image->dimensions();
                $variants[$label] = ['path' => $variant, 'width' => $w, 'height' => $h];
            }$record = $property->images()->make();
            $record->forceFill(['disk' => $disk, 'path' => $path, 'alt_text' => $alt, 'mime_type' => 'image/webp', 'file_size' => Storage::disk($disk)->size($path), 'width' => $width, 'height' => $height, 'variants' => $variants, 'processing_status' => ImageProcessingStatus::Ready, 'sort_order' => $order])->save();

            return $record;
        } catch (Throwable $e) {
            Storage::disk($disk)->delete($paths);
            throw $e;
        }
    }

    public function deleteFiles(PropertyImage $image): void
    {
        $paths = [$image->path];
        foreach ($image->variants ?? [] as $variant) {
            if (is_array($variant) && isset($variant['path'])) {
                $paths[] = $variant['path'];
            }
        }Storage::disk($image->disk)->delete(array_unique($paths));
    }
}
