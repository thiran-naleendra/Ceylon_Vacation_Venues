<?php

namespace App\Http\Requests\Admin\Packages;

use App\Enums\PackageAvailability;
use App\Enums\PublicationStatus;
use App\Models\TourPackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTourPackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', TourPackage::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(PublicationStatus::class)],
            'availability' => ['nullable', Rule::enum(PackageAvailability::class)],
            'featured' => ['nullable', 'boolean'],
        ];
    }
}
