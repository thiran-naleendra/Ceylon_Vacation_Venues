<?php

namespace Database\Factories;

use App\Enums\ImageProcessingStatus;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<VehicleImage> */
class VehicleImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
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
