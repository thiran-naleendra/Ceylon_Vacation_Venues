<?php

namespace App\Http\Requests\Admin\Packages;

use App\Enums\PublicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTourPackageStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('package')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(PublicationStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                if (! $this->hasAny(['status', 'is_featured'])) {
                    $validator->errors()->add('status', 'Choose a package status action.');
                }
            },
        ];
    }
}
