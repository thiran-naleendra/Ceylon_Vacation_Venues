<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\PropertyType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PropertyType> */ class PropertyTypeFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->unique()->words(2, true), 'description' => fake()->sentence()];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => PublicationStatus::Published, 'published_at' => now()->subMinute()]);
    }
}
