<?php

namespace App\Http\Requests\Admin\Gallery;

use App\Models\GalleryAlbum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGalleryCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GalleryAlbum::class) ?? false;
    }

    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('gallery_albums', 'slug')], 'description' => ['nullable', 'string', 'max:2000'], 'status' => ['required', Rule::in(['draft', 'published'])], 'sort_order' => ['required', 'integer', 'min:0']];
    }
}
