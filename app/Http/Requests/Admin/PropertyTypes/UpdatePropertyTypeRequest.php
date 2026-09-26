<?php

namespace App\Http\Requests\Admin\PropertyTypes;

use Illuminate\Validation\Rule;

class UpdatePropertyTypeRequest extends StorePropertyTypeRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('property_type')) ?? false;
    }

    public function rules(): array
    {
        $r = parent::rules();
        $r['slug'] = ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('property_types', 'slug')->ignore($this->route('property_type'))];

        return $r;
    }
}
