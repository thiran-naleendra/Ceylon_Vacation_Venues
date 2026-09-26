<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class BrandAssetService
{
    public function store(UploadedFile $upload, string $kind): string
    {
        $disk = 'public';
        $basename = (string) Str::uuid();
        $extension = $kind === 'favicon' ? 'png' : 'webp';
        $image = Image::fromUpload($upload)->orient();
        $image = $kind === 'favicon' ? $image->cover(512, 512)->optimize('png') : $image->scale(width: 800)->optimize('webp', 86);
        $path = $image->storePubliclyAs('branding', $basename.'.'.$extension, $disk);
        if ($path === false) {
            throw new RuntimeException('The branding image could not be stored.');
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
