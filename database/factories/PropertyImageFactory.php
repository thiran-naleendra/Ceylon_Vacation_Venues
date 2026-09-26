<?php

namespace Database\Factories;

use App\Enums\ImageProcessingStatus;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PropertyImage> */ class PropertyImageFactory extends Factory
{
    public function definition(): array
    {
        return ['property_id' => Property::factory(), 'disk' => 'public', 'path' => 'properties/test.webp', 'alt_text' => 'Property exterior', 'mime_type' => 'image/webp', 'file_size' => 1000, 'width' => 1200, 'height' => 900, 'processing_status' => ImageProcessingStatus::Ready];
    }
}
