<?php

namespace Database\Factories;

use App\Models\PackageItinerary;
use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PackageItinerary> */
class PackageItineraryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tour_package_id' => TourPackage::factory(),
            'day_number' => 1,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
        ];
    }
}
