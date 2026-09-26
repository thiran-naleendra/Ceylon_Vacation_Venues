<?php

namespace App\Http\Requests\Public;

use App\Enums\PackageAvailability;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTourPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'destination' => ['nullable', 'string', 'max:120'],
            'availability' => ['nullable', Rule::enum(PackageAvailability::class)],
            'duration' => ['nullable', Rule::in(['1-3', '4-7', '8+'])],
            'sort' => ['nullable', Rule::in(['recommended', 'price_low', 'price_high', 'duration_short', 'duration_long'])],
        ];
    }
}
