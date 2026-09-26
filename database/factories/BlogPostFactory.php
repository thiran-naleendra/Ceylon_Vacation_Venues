<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlogPost> */
class BlogPostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'blog_category_id' => BlogCategory::factory(),
            'author_id' => User::factory(),
            'title' => fake()->unique()->sentence(4),
            'body' => fake()->paragraphs(3, true),
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
