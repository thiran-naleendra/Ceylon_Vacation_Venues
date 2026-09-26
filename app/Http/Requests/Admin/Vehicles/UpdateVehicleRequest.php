<?php

namespace App\Http\Requests\Admin\Vehicles;

use App\Models\Vehicle;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateVehicleRequest extends StoreVehicleRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->vehicle()) ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('vehicles', 'slug')->ignore($this->vehicle())];
        $rules['existing_images'] = ['nullable', 'array'];
        $rules['existing_images.*.alt_text'] = ['required', 'string', 'max:255'];
        $rules['existing_images.*.sort_order'] = ['required', 'integer', 'min:0'];
        $rules['delete_image_ids'] = ['nullable', 'array'];
        $rules['delete_image_ids.*'] = ['integer', Rule::exists('vehicle_images', 'id')->where('vehicle_id', $this->vehicle()->getKey())];
        $rules['featured_image_id'] = ['nullable', 'integer', Rule::exists('vehicle_images', 'id')->where('vehicle_id', $this->vehicle()->getKey())];

        return $rules;
    }

    public function after(): array
    {
        return [...parent::after(), function (Validator $validator): void {
            $deleted = array_map('intval', $this->input('delete_image_ids', []));
            if ($this->filled('featured_image_id') && in_array($this->integer('featured_image_id'), $deleted, true)) {
                $validator->errors()->add('featured_image_id', 'The featured image cannot also be deleted.');
            }
            if ($this->hasFile('featured_image') && $this->filled('featured_image_id')) {
                $validator->errors()->add('featured_image_id', 'Choose an existing featured image or upload a replacement, not both.');
            }
        }];
    }

    private function vehicle(): Vehicle
    {
        return $this->route('vehicle');
    }
}
