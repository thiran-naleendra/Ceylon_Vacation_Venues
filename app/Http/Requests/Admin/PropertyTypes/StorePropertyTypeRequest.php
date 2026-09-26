<?php

namespace App\Http\Requests\Admin\PropertyTypes;

use App\Enums\PublicationStatus;
use App\Models\PropertyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PropertyType::class) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('property_types', 'slug')], 'description' => ['nullable', 'string', 'max:2000'], 'status' => ['required', Rule::enum(PublicationStatus::class)], 'sort_order' => ['required', 'integer', 'min:0']];
    }
}
