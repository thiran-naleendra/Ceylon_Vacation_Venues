<?php

namespace App\Http\Requests\Admin\Inquiries;

use App\Enums\InquiryStatus;
use App\Enums\InquiryType;
use App\Models\Inquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Inquiry::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::enum(InquiryType::class)],
            'status' => ['nullable', Rule::enum(InquiryStatus::class)],
        ];
    }
}
