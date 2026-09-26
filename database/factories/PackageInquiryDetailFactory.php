<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\PackageInquiryDetail;
use App\Models\TourPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PackageInquiryDetail> */
class PackageInquiryDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inquiry_id' => Inquiry::factory()->package(),
            'tour_package_id' => TourPackage::factory(),
            'package_title_snapshot' => fn (array $attributes) => TourPackage::findOrFail($attributes['tour_package_id'])->title,
            'adults' => 2,
            'children' => 0,
        ];
    }
}
