<?php

namespace Database\Factories;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Models\InquiryStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InquiryStatusHistory> */
class InquiryStatusHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inquiry_id' => Inquiry::factory(),
            'changed_by' => User::factory(),
            'from_status' => null,
            'to_status' => InquiryStatus::New,
        ];
    }
}
