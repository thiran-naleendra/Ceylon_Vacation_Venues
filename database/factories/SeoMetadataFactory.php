<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SeoMetadata> */
class SeoMetadataFactory extends Factory
{
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'meta_title' => fake()->sentence(4),
            'meta_description' => fake()->paragraph(),
        ];
    }
}
