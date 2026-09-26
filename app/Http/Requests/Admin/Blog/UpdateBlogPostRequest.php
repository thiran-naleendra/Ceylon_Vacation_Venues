<?php

namespace App\Http\Requests\Admin\Blog;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateBlogPostRequest extends StoreBlogPostRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('post')) ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('blog_posts', 'slug')->ignore($this->route('post'))];
        $rules['featured_image'][0] = 'nullable';

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->hasFile('featured_image') && $this->boolean('remove_featured_image')) {
                $validator->errors()->add('featured_image', 'Upload a replacement or remove the current image, not both.');
            }
        }];
    }
}
