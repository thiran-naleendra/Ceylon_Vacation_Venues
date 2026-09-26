<?php

namespace Database\Factories;

use App\Enums\InquiryType;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Inquiry> */
class InquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => InquiryType::General,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'message' => fake()->paragraph(),
            'privacy_notice_version' => '1.0',
            'privacy_acknowledged_at' => now(),
        ];
    }

    public function package(): static
    {
        return $this->state(fn (): array => ['type' => InquiryType::Package]);
    }

    public function rental(): static
    {
        return $this->state(fn (): array => ['type' => InquiryType::Rental]);
    }

    public function visa(): static
    {
        return $this->state(fn (): array => ['type' => InquiryType::Visa]);
    }

    public function baggage(): static
    {
        return $this->state(fn (): array => ['type' => InquiryType::Baggage]);
    }
}
