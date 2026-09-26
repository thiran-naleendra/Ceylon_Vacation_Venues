<?php

namespace Database\Factories;

use App\Models\WebsiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WebsiteSetting> */
class WebsiteSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group' => 'contact',
            'key' => 'contact.email',
            'value' => 'hello@example.com',
        ];
    }
}
