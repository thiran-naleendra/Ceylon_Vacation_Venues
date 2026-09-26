<?php

namespace App\Http\Requests\Admin\Properties;

use App\Enums\PublicationStatus;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Property::class) ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'type' => ['nullable', 'integer', Rule::exists('property_types', 'id')], 'location' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', Rule::enum(PublicationStatus::class)], 'featured' => ['nullable', 'boolean']];
    }
}
