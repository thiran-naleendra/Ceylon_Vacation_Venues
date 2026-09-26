<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\RentalInquiryDetail;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RentalInquiryDetail> */
class RentalInquiryDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inquiry_id' => Inquiry::factory()->rental(),
            'vehicle_id' => Vehicle::factory(),
            'vehicle_title_snapshot' => fn (array $attributes) => Vehicle::findOrFail($attributes['vehicle_id'])->title,
            'pickup_at' => now()->addWeek(),
            'return_at' => now()->addWeeks(2),
        ];
    }
}
