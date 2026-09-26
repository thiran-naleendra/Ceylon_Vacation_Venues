<?php

namespace Database\Seeders;

use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;

class VehicleCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['car' => 'Car', 'tuk-tuk' => 'Tuk Tuk', 'bike' => 'Bike'] as $slug => $name) {
            $category = VehicleCategory::firstOrCreate(['slug' => $slug], ['name' => $name]);
            $category->forceFill(['status' => 'published', 'published_at' => $category->published_at ?? now()])->save();
        }
    }
}
