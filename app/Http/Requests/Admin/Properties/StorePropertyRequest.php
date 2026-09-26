<?php

namespace App\Http\Requests\Admin\Properties;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Property::class) ?? false;
    }

    public function rules(): array
    {
        return ['property_type_id' => ['required', 'integer', Rule::exists('property_types', 'id')], 'name' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('properties', 'slug')], 'short_description' => ['nullable', 'string', 'max:500'], 'description' => ['nullable', 'string', 'max:50000'], 'location' => ['required', 'string', 'max:255'], 'address_description' => ['nullable', 'string', 'max:2000'], 'price' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999.99'], 'currency' => ['required', 'string', 'size:3', 'alpha:ascii'], 'pricing_unit' => ['required', Rule::in(['per_night', 'per_week', 'per_month', 'on_request'])], 'bedrooms' => ['nullable', 'integer', 'min:0', 'max:100'], 'bathrooms' => ['nullable', 'decimal:0,1', 'min:0', 'max:100'], 'max_guests' => ['nullable', 'integer', 'min:1', 'max:500'], 'beds_details' => ['nullable', 'string', 'max:2000'], 'availability_information' => ['nullable', 'string', 'max:3000'], 'check_in_time' => ['nullable', 'date_format:H:i'], 'check_out_time' => ['nullable', 'date_format:H:i'], 'amenity_ids' => ['nullable', 'array'], 'amenity_ids.*' => ['integer', Rule::exists('amenities', 'id')->where('is_active', true)], 'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'], 'featured_image' => ['nullable', $this->imageRule()], 'featured_image_alt' => ['nullable', 'string', 'max:255', 'required_with:featured_image'], 'gallery_images' => ['nullable', 'array', 'max:16'], 'gallery_images.*' => [$this->imageRule()], 'gallery_alt_text' => ['nullable', 'array', 'max:16'], 'gallery_alt_text.*' => ['required', 'string', 'max:255'], 'seo.meta_title' => ['nullable', 'string', 'max:255'], 'seo.meta_description' => ['nullable', 'string', 'max:500'], 'seo.canonical_url' => ['nullable', 'url:https', 'max:2048'], 'seo.robots_index' => ['required', 'boolean'], 'seo.robots_follow' => ['required', 'boolean'], 'seo.og_title' => ['nullable', 'string', 'max:255'], 'seo.og_description' => ['nullable', 'string', 'max:500'], 'seo.og_image_alt' => ['nullable', 'string', 'max:255'], 'seo_use_featured_image' => ['nullable', 'boolean']];
    }

    public function after(): array
    {
        return [function (Validator $v): void {
            if (count($this->file('gallery_images', [])) !== count($this->input('gallery_alt_text', []))) {
                $v->errors()->add('gallery_alt_text', 'Provide alt text for every gallery image.');
            }
        }];
    }

    protected function imageRule(): File
    {
        return File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('8mb')->dimensions(Rule::dimensions()->minWidth(400)->minHeight(300)->maxWidth(8000)->maxHeight(8000));
    }
}
