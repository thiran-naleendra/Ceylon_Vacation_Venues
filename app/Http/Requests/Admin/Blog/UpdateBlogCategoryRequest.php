<?php

namespace App\Http\Requests\Admin\Blog;

use Illuminate\Validation\Rule;

class UpdateBlogCategoryRequest extends StoreBlogCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('blog_category')) ?? false;
    }

    public function rules(): array
    {
        $r = parent::rules();
        $r['slug'] = ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('blog_categories', 'slug')->ignore($this->route('blog_category'))];

        return $r;
    }
}
