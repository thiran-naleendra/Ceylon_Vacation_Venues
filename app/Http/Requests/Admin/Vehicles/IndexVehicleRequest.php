<?php

namespace App\Http\Requests\Admin\Vehicles;

use App\Enums\PublicationStatus;
use App\Enums\VehicleAvailability;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Vehicle::class) ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::enum(PublicationStatus::class)], 'availability' => ['nullable', Rule::enum(VehicleAvailability::class)], 'category' => ['nullable', 'integer', Rule::exists('vehicle_categories', 'id')], 'featured' => ['nullable', 'boolean']];
    }
}
