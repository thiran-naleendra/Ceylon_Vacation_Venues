<?php

namespace App\Http\Requests\Public;

use App\Enums\VehicleAvailability;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:180', 'exists:vehicle_categories,slug'],
            'availability' => ['nullable', Rule::enum(VehicleAvailability::class)],
            'transmission' => ['nullable', 'string', 'max:30'],
            'passengers' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort' => ['nullable', Rule::in(['recommended', 'price_low', 'price_high', 'capacity'])],
        ];
    }
}
