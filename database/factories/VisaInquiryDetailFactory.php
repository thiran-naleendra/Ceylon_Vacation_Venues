<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\VisaInquiryDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VisaInquiryDetail> */
class VisaInquiryDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inquiry_id' => Inquiry::factory()->visa(),
            'nationality_code' => 'GB',
            'arrival_date' => today()->subWeek(),
            'current_visa_expiry_date' => today()->addWeeks(3),
            'requested_extension_days' => 30,
        ];
    }
}
