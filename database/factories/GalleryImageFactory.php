<?php

namespace Database\Factories;

use App\Enums\ImageProcessingStatus;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<GalleryImage> */
class GalleryImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'gallery_album_id' => GalleryAlbum::factory(),
            'title' => fake()->sentence(3),
            'disk' => 'public',
            'path' => 'images/'.Str::uuid().'.webp',
            'alt_text' => fake()->sentence(),
            'mime_type' => 'image/webp',
            'file_size' => 12000,
            'width' => 1200,
            'height' => 800,
            'processing_status' => ImageProcessingStatus::Ready,
        ];
    }
}
