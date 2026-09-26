<?php

namespace App\Http\Requests\Public;

use App\Enums\InquiryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+() .-]+$/'],
            'country_code' => ['nullable', 'string', 'size:2', 'alpha:ascii'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'privacy_accepted' => ['accepted'],
            'website' => ['nullable', 'max:0'],
            'form_token' => ['required', 'uuid'],
        ];

        return [...$rules, ...match ($this->inquiryType()) {
            InquiryType::Package => [
                'preferred_start_date' => ['nullable', 'date', 'after_or_equal:today'],
                'preferred_end_date' => ['nullable', 'date', 'after_or_equal:preferred_start_date'],
                'adults' => ['required', 'integer', 'min:1', 'max:100'],
                'children' => ['required', 'integer', 'min:0', 'max:100'],
            ],
            InquiryType::Rental => [
                'pickup_at' => ['required', 'date', 'after:now'],
                'return_at' => ['required', 'date', 'after:pickup_at'],
                'pickup_location' => ['required', 'string', 'max:255'],
                'return_location' => ['required', 'string', 'max:255'],
                'driver_required' => ['required', 'boolean'],
            ],
            InquiryType::Visa => [
                'nationality_code' => ['required', 'string', 'size:2', 'alpha:ascii'],
                'arrival_date' => ['required', 'date'],
                'current_visa_expiry_date' => ['required', 'date', 'after_or_equal:today'],
                'requested_extension_days' => ['required', 'integer', Rule::in([30, 60, 90])],
            ],
            InquiryType::Baggage => [
                'pickup_location' => ['required', 'string', 'max:255'],
                'delivery_location' => ['required', 'string', 'max:255'],
                'pickup_at' => ['required', 'date', 'after:now'],
                'bag_count' => ['required', 'integer', 'min:1', 'max:100'],
                'estimated_weight_kg' => ['nullable', 'decimal:0,2', 'min:0.01', 'max:999999.99'],
                'special_instructions' => ['nullable', 'string', 'max:3000'],
            ],
            InquiryType::General => [
                'subject' => ['required', 'string', 'max:255'],
                'message' => ['required', 'string', 'min:10', 'max:5000'],
            ],
            InquiryType::Property => [
                'check_in_date' => ['required', 'date', 'after_or_equal:today'],
                'check_out_date' => ['required', 'date', 'after:check_in_date'],
                'guests' => ['required', 'integer', 'min:1', 'max:500'],
            ],
        }];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $token = $this->string('form_token')->toString();
            $startedAt = $this->session()->get("inquiry_form_tokens.{$token}");

            if (! is_int($startedAt) || $startedAt > now()->subSeconds(2)->timestamp || $startedAt < now()->subHours(2)->timestamp) {
                $validator->errors()->add('form_token', 'Please reload the form and try again.');
            }
        }];
    }

    public function inquiryType(): InquiryType
    {
        return InquiryType::from((string) $this->route('inquiry_type'));
    }
}
