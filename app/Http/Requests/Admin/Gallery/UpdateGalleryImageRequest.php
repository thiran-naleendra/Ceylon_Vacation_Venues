<?php

namespace App\Http\Requests\Admin\Gallery;

class UpdateGalleryImageRequest extends StoreGalleryImageRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('gallery')) ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['image'][0] = 'nullable';

        return $rules;
    }
}
