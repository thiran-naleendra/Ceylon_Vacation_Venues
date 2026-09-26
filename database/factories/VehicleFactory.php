<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Enums\VehicleAvailability;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Vehicle> */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicle_category_id' => VehicleCategory::factory()->published(),
            'title' => fake()->unique()->words(3, true),
            'rental_rate' => '49.95',
            'currency' => 'USD',
            'rate_unit' => 'day',
            'summary' => fake()->sentence(),
            'seats' => 4,
            'luggage_capacity' => 2,
            'has_air_conditioning' => true,
            'availability_status' => VehicleAvailability::Available,
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
