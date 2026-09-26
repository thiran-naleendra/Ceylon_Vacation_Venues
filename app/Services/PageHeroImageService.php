<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PageHeroImageService
{
    /** @return array{disk: string, path: string, width: int, height: int, variants: array<string, array{path: string, width: int, height: int}>, alt: string} */
    public function store(string $pageKey, UploadedFile $upload, string $alt): array
    {
        $disk = 'public';
        $directory = 'pages/'.$pageKey;
        $basename = (string) Str::uuid();
        $paths = [];

        try {
            $large = Image::fromUpload($upload)->orient()->scale(width: 1920)->optimize('webp', 82);
            $path = $large->storePubliclyAs($directory, $basename.'.webp', $disk);
            if ($path === false) {
                throw new RuntimeException('The hero image could not be stored.');
            }
            $paths[] = $path;
            [$imageWidth, $imageHeight] = $large->dimensions();
            $variants = [];
            foreach ([640 => 'small', 1280 => 'medium'] as $width => $label) {
                $variant = Image::fromUpload($upload)->orient()->scale(width: $width)->optimize('webp', 80);
                $variantPath = $variant->storePubliclyAs($directory, $basename.'-'.$width.'.webp', $disk);
                if ($variantPath === false) {
                    throw new RuntimeException('A hero image variant could not be stored.');
                }
                $paths[] = $variantPath;
                [$actualWidth, $height] = $variant->dimensions();
                $variants[$label] = ['path' => $variantPath, 'width' => $actualWidth, 'height' => $height];
            }

            return ['disk' => $disk, 'path' => $path, 'width' => $imageWidth, 'height' => $imageHeight, 'variants' => $variants, 'alt' => $alt];
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($paths);
            throw $exception;
        }
    }

    /** @param array<string, mixed>|null $image */
    public function delete(?array $image): void
    {
        if (! $image || ! isset($image['path'])) {
            return;
        }
        $paths = [$image['path']];
        foreach ($image['variants'] ?? [] as $variant) {
            if (is_array($variant) && isset($variant['path'])) {
                $paths[] = $variant['path'];
            }
        }
        Storage::disk($image['disk'] ?? 'public')->delete(array_unique($paths));
    }
}
