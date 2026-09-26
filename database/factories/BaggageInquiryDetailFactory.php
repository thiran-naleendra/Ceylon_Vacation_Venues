<?php

namespace Database\Factories;

use App\Models\BaggageInquiryDetail;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BaggageInquiryDetail> */
class BaggageInquiryDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inquiry_id' => Inquiry::factory()->baggage(),
            'pickup_location' => 'Colombo',
            'delivery_location' => 'Galle',
            'bag_count' => 2,
            'estimated_weight_kg' => '20.50',
        ];
    }
}
