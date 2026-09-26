<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Page> */
class PageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'page_key' => fake()->unique()->slug(2),
            'title' => fake()->unique()->sentence(3),
            'template' => 'standard',
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
