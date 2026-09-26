<?php

namespace App\Http\Requests\Admin\Packages;

use App\Enums\PackageAvailability;
use App\Models\TourPackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class StoreTourPackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', TourPackage::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('tour_packages', 'slug')],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:50000'],
            'destination' => ['nullable', 'string', 'max:255'],
            'availability_status' => ['required', Rule::enum(PackageAvailability::class)],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'duration_nights' => ['nullable', 'integer', 'min:0', 'max:365', 'lte:duration_days'],
            'starting_price' => ['nullable', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'currency' => ['required', 'string', 'size:3', 'alpha:ascii'],
            'price_basis' => ['required', Rule::in(['per_person', 'per_group', 'per_package'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],

            'itineraries' => ['nullable', 'array', 'max:60'],
            'itineraries.*.day_number' => ['required', 'integer', 'min:1', 'max:365', 'distinct'],
            'itineraries.*.title' => ['required', 'string', 'max:255'],
            'itineraries.*.description' => ['nullable', 'string', 'max:10000'],

            'featured_image' => ['nullable', $this->imageRule()],
            'featured_image_alt' => ['nullable', 'string', 'max:255', 'required_with:featured_image'],
            'gallery_images' => ['nullable', 'array', 'max:12'],
            'gallery_images.*' => [$this->imageRule()],
            'gallery_alt_text' => ['nullable', 'array', 'max:12'],
            'gallery_alt_text.*' => ['required', 'string', 'max:255'],

            'seo.meta_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:500'],
            'seo.canonical_url' => ['nullable', 'url:https', 'max:2048'],
            'seo.robots_index' => ['required', 'boolean'],
            'seo.robots_follow' => ['required', 'boolean'],
            'seo.og_title' => ['nullable', 'string', 'max:255'],
            'seo.og_description' => ['nullable', 'string', 'max:500'],
            'seo.og_image_alt' => ['nullable', 'string', 'max:255'],
            'seo_use_featured_image' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $images = $this->file('gallery_images', []);
                $altText = $this->input('gallery_alt_text', []);

                if (count($images) !== count($altText)) {
                    $validator->errors()->add('gallery_alt_text', 'Provide alt text for every gallery image.');
                }
            },
        ];
    }

    protected function imageRule(): File
    {
        return File::image()
            ->types(['jpg', 'jpeg', 'png', 'webp'])
            ->max('8mb')
            ->dimensions(Rule::dimensions()->minWidth(400)->minHeight(300)->maxWidth(8000)->maxHeight(8000));
    }
}
