<?php

namespace App\Http\Requests\Admin\Properties;

use App\Models\Property;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePropertyRequest extends StorePropertyRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->property()) ?? false;
    }

    public function rules(): array
    {
        $r = parent::rules();
        $r['slug'] = ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('properties', 'slug')->ignore($this->property())];
        $r['existing_images'] = ['nullable', 'array'];
        $r['existing_images.*.alt_text'] = ['required', 'string', 'max:255'];
        $r['existing_images.*.caption'] = ['nullable', 'string', 'max:1000'];
        $r['existing_images.*.sort_order'] = ['required', 'integer', 'min:0'];
        $r['delete_image_ids'] = ['nullable', 'array'];
        $r['delete_image_ids.*'] = ['integer', Rule::exists('property_images', 'id')->where('property_id', $this->property()->id)];
        $r['featured_image_id'] = ['nullable', 'integer', Rule::exists('property_images', 'id')->where('property_id', $this->property()->id)];

        return $r;
    }

    public function after(): array
    {
        return [...parent::after(), function (Validator $v): void {
            $deleted = array_map('intval', $this->input('delete_image_ids', []));
            if ($this->filled('featured_image_id') && in_array($this->integer('featured_image_id'), $deleted, true)) {
                $v->errors()->add('featured_image_id', 'The featured image cannot also be deleted.');
            }
        }];
    }

    private function property(): Property
    {
        return $this->route('property');
    }
}
