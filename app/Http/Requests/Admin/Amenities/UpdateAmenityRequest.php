<?php

namespace App\Http\Requests\Admin\Amenities;

use Illuminate\Validation\Rule;

class UpdateAmenityRequest extends StoreAmenityRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('amenity')) ?? false;
    }

    public function rules(): array
    {
        $r = parent::rules();
        $r['slug'] = ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('amenities', 'slug')->ignore($this->route('amenity'))];

        return $r;
    }
}
