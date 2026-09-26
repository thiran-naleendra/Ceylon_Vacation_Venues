<?php

namespace App\Http\Requests\Admin\Gallery;

use App\Models\GalleryImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreGalleryImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GalleryImage::class) ?? false;
    }

    public function rules(): array
    {
        return ['gallery_album_id' => ['required', 'integer', Rule::exists('gallery_albums', 'id')], 'title' => ['required', 'string', 'max:255'], 'alt_text' => ['required', 'string', 'max:255'], 'caption' => ['nullable', 'string', 'max:2000'], 'sort_order' => ['required', 'integer', 'min:0'], 'status' => ['required', Rule::in(['draft', 'published'])], 'image' => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('8mb')->dimensions(Rule::dimensions()->minWidth(400)->minHeight(300)->maxWidth(8000)->maxHeight(8000))]];
    }
}
