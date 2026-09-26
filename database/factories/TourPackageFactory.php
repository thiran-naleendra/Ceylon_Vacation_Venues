<?php

namespace Database\Factories;

use App\Enums\PackageAvailability;
use App\Enums\PublicationStatus;
use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TourPackage> */
class TourPackageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(4),
            'duration_days' => 5,
            'duration_nights' => 4,
            'starting_price' => '199.99',
            'currency' => 'USD',
            'destination' => fake()->city(),
            'availability_status' => PackageAvailability::Available,
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
