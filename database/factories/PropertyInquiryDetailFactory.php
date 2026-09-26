<?php

namespace Database\Factories;

use App\Enums\InquiryType;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\PropertyInquiryDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PropertyInquiryDetail> */ class PropertyInquiryDetailFactory extends Factory
{
    public function definition(): array
    {
        return ['inquiry_id' => Inquiry::factory()->state(['type' => InquiryType::Property]), 'property_id' => Property::factory(), 'property_name_snapshot' => 'Test Villa', 'check_in_date' => now()->addWeek()->toDateString(), 'check_out_date' => now()->addWeeks(2)->toDateString(), 'guests' => 2];
    }
}
