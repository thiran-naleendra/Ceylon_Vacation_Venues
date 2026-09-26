<?php

namespace App\Http\Requests\Admin\Vehicles;

use App\Enums\PublicationStatus;
use App\Models\VehicleCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', VehicleCategory::class) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('vehicle_categories', 'slug')], 'description' => ['nullable', 'string', 'max:5000'], 'status' => ['required', Rule::enum(PublicationStatus::class)], 'sort_order' => ['required', 'integer', 'min:0']];
    }
}
