<?php

namespace App\Http\Requests\Admin\Vehicles;

use App\Enums\VehicleAvailability;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Vehicle::class) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'vehicle_category_id' => ['required', 'integer', Rule::exists('vehicle_categories', 'id')],
            'title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('vehicles', 'slug')],
            'summary' => ['nullable', 'string', 'max:500'], 'description' => ['nullable', 'string', 'max:50000'],
            'rental_rate' => ['required', 'decimal:0,2', 'min:0', 'max:9999999999.99'], 'currency' => ['required', 'string', 'size:3', 'alpha:ascii'],
            'rate_unit' => ['required', Rule::in(['hour', 'day', 'week', 'month'])], 'transmission' => ['nullable', Rule::in(['automatic', 'manual', 'semi_automatic'])],
            'seats' => ['nullable', 'integer', 'min:1', 'max:100'], 'luggage_capacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'has_air_conditioning' => ['required', 'boolean'], 'availability_status' => ['required', Rule::enum(VehicleAvailability::class)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'featured_image' => ['nullable', $this->imageRule()], 'featured_image_alt' => ['nullable', 'string', 'max:255', 'required_with:featured_image'],
            'gallery_images' => ['nullable', 'array', 'max:12'], 'gallery_images.*' => [$this->imageRule()],
            'gallery_alt_text' => ['nullable', 'array', 'max:12'], 'gallery_alt_text.*' => ['required', 'string', 'max:255'],
            'seo.meta_title' => ['nullable', 'string', 'max:255'], 'seo.meta_description' => ['nullable', 'string', 'max:500'],
            'seo.canonical_url' => ['nullable', 'url:https', 'max:2048'], 'seo.robots_index' => ['required', 'boolean'], 'seo.robots_follow' => ['required', 'boolean'],
            'seo.og_title' => ['nullable', 'string', 'max:255'], 'seo.og_description' => ['nullable', 'string', 'max:500'], 'seo.og_image_alt' => ['nullable', 'string', 'max:255'],
            'seo_use_featured_image' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (count($this->file('gallery_images', [])) !== count($this->input('gallery_alt_text', []))) {
                $validator->errors()->add('gallery_alt_text', 'Provide alt text for every gallery image.');
            }
        }];
    }

    protected function imageRule(): File
    {
        return File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('8mb')->dimensions(Rule::dimensions()->minWidth(400)->minHeight(300)->maxWidth(8000)->maxHeight(8000));
    }
}
