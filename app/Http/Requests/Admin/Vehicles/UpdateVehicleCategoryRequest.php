<?php

namespace App\Http\Requests\Admin\Vehicles;

use Illuminate\Validation\Rule;

class UpdateVehicleCategoryRequest extends StoreVehicleCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('vehicle_category')) ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('vehicle_categories', 'slug')->ignore($this->route('vehicle_category'))];

        return $rules;
    }
}
