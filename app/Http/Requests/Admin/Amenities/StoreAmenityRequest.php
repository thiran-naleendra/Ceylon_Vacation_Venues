<?php

namespace App\Http\Requests\Admin\Amenities;

use App\Models\Amenity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAmenityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Amenity::class) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('amenities', 'slug')], 'description' => ['nullable', 'string', 'max:2000'], 'is_active' => ['required', 'boolean'], 'sort_order' => ['required', 'integer', 'min:0']];
    }
}
