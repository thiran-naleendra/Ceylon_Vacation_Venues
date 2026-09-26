<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\VehicleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VehicleCategory> */
class VehicleCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->subMinute(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->addDay(),
        ]);
    }
}
