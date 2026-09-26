<?php

namespace App\Http\Requests\Admin\Pages;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('page')) ?? false;
    }

    public function rules(): array
    {
        $page = $this->route('page');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('pages', 'slug')->ignore($page)],
            'summary' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:100000'],
            'hero.title' => ['nullable', 'string', 'max:255'],
            'hero.text' => ['nullable', 'string', 'max:1000'],
            'hero.image' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('8mb')->dimensions(Rule::dimensions()->minWidth(800)->minHeight(400)->maxWidth(8000)->maxHeight(8000))],
            'hero.image_alt' => ['nullable', 'string', 'max:255', 'required_with:hero.image'],
            'hero.delete_image' => ['nullable', 'boolean'],
            'seo.meta_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:500'],
            'seo.canonical_url' => ['nullable', 'url:https', 'max:2048'],
            'seo.robots_index' => ['required', 'boolean'],
            'seo.robots_follow' => ['required', 'boolean'],
            'seo.og_title' => ['nullable', 'string', 'max:255'],
            'seo.og_description' => ['nullable', 'string', 'max:500'],
            'seo.og_image_alt' => ['nullable', 'string', 'max:255'],
            'seo_use_hero_image' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->hasFile('hero.image') && $this->boolean('hero.delete_image')) {
                $validator->errors()->add('hero.image', 'Upload a replacement or remove the current image, not both.');
            }
        }];
    }
}
