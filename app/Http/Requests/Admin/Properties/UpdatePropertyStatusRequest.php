<?php

namespace App\Http\Requests\Admin\Properties;

use App\Enums\PublicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $p = $this->route('property');

        return $this->user()?->can('update', $p) ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['sometimes', Rule::enum(PublicationStatus::class)], 'is_featured' => ['sometimes', 'boolean']];
    }
}
