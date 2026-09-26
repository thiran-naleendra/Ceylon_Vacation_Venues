<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class BlogImageService
{
    /** @return array{path: string, variants: array<string, array{path: string, width: int, height: int}>} */
    public function store(UploadedFile $upload): array
    {
        $basename = (string) Str::uuid();
        $storedPaths = [];

        try {
            $image = Image::fromUpload($upload)->orient()->scale(width: 1800)->optimize('webp', 84);
            $path = $image->storePubliclyAs('blog', $basename.'.webp', 'public');
            if ($path === false) {
                throw new RuntimeException('The blog image could not be stored.');
            }
            $storedPaths[] = $path;
            $variants = [];

            foreach ([480 => 'small', 960 => 'medium'] as $width => $label) {
                $variant = Image::fromUpload($upload)->orient()->scale(width: $width)->optimize('webp', 80);
                $variantPath = $variant->storePubliclyAs('blog', $basename.'-'.$width.'.webp', 'public');
                if ($variantPath === false) {
                    throw new RuntimeException('A responsive blog image could not be stored.');
                }
                $storedPaths[] = $variantPath;
                [$actualWidth, $actualHeight] = $variant->dimensions();
                $variants[$label] = ['path' => $variantPath, 'width' => $actualWidth, 'height' => $actualHeight];
            }

            return ['path' => $path, 'variants' => $variants];
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }
    }

    /** @param array<string, array{path?: string}>|null $variants */
    public function delete(?string $path, ?array $variants = null): void
    {
        $paths = array_filter([$path]);
        foreach ($variants ?? [] as $variant) {
            if (isset($variant['path'])) {
                $paths[] = $variant['path'];
            }
        }

        Storage::disk('public')->delete(array_unique($paths));
    }
}
