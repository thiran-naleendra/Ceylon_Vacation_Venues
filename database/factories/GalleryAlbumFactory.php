<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\GalleryAlbum;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GalleryAlbum> */
class GalleryAlbumFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
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
