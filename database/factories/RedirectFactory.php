<?php

namespace Database\Factories;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Redirect> */
class RedirectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'source_path' => '/old-'.fake()->unique()->slug(3),
            'destination_path' => '/tour-packages',
        ];
    }
}
