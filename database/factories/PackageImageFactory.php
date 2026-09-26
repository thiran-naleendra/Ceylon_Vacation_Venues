<?php

namespace Database\Factories;

use App\Enums\ImageProcessingStatus;
use App\Models\PackageImage;
use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<PackageImage> */
class PackageImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tour_package_id' => TourPackage::factory(),
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
