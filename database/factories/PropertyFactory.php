<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Property> */ class PropertyFactory extends Factory
{
    public function definition(): array
    {
        return ['property_type_id' => PropertyType::factory()->published(), 'name' => fake()->unique()->words(3, true), 'short_description' => fake()->sentence(), 'description' => fake()->paragraph(), 'location' => 'Mirissa', 'price' => '125.00', 'currency' => 'USD', 'pricing_unit' => 'per_night', 'bedrooms' => 2, 'bathrooms' => '2.0', 'max_guests' => 4];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => PublicationStatus::Published, 'published_at' => now()->subMinute()]);
    }
}
