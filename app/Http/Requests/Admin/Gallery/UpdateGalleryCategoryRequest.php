<?php

namespace App\Http\Requests\Admin\Gallery;

use Illuminate\Validation\Rule;

class UpdateGalleryCategoryRequest extends StoreGalleryCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('gallery_category')) ?? false;
    }

    public function rules(): array
    {
        $r = parent::rules();
        $r['slug'] = ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('gallery_albums', 'slug')->ignore($this->route('gallery_category'))];

        return $r;
    }
}
