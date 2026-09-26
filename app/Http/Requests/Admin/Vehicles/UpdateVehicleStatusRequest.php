<?php

namespace App\Http\Requests\Admin\Vehicles;

use App\Enums\PublicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('vehicle')) ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['sometimes', Rule::enum(PublicationStatus::class)], 'is_featured' => ['sometimes', 'boolean']];
    }
}
