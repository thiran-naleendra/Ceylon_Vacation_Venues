<?php

namespace App\Http\Requests\Admin\Packages;

use App\Models\TourPackage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTourPackageRequest extends StoreTourPackageRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->package()) ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = [
            'nullable', 'string', 'max:180', 'alpha_dash:ascii',
            Rule::unique('tour_packages', 'slug')->ignore($this->package()),
        ];
        $rules['existing_images'] = ['nullable', 'array'];
        $rules['existing_images.*.alt_text'] = ['required', 'string', 'max:255'];
        $rules['existing_images.*.sort_order'] = ['required', 'integer', 'min:0'];
        $rules['delete_image_ids'] = ['nullable', 'array'];
        $rules['delete_image_ids.*'] = [
            'integer',
            Rule::exists('package_images', 'id')->where('tour_package_id', $this->package()->getKey()),
        ];
        $rules['featured_image_id'] = [
            'nullable', 'integer',
            Rule::exists('package_images', 'id')->where('tour_package_id', $this->package()->getKey()),
        ];

        return $rules;
    }

    public function after(): array
    {
        return [
            ...parent::after(),
            function (Validator $validator): void {
                $deleteIds = array_map('intval', $this->input('delete_image_ids', []));
                if ($this->filled('featured_image_id') && in_array($this->integer('featured_image_id'), $deleteIds, true)) {
                    $validator->errors()->add('featured_image_id', 'The featured image cannot also be deleted.');
                }

                if ($this->hasFile('featured_image') && $this->filled('featured_image_id')) {
                    $validator->errors()->add('featured_image_id', 'Choose an existing featured image or upload a replacement, not both.');
                }
            },
        ];
    }

    private function package(): TourPackage
    {
        return $this->route('package');
    }
}
