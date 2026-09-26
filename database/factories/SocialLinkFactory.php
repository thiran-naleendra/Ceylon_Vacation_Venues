<?php

namespace Database\Factories;

use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SocialLink> */
class SocialLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'platform' => 'instagram',
            'url' => 'https://www.instagram.com/example/',
            'label' => 'Instagram',
        ];
    }
}
