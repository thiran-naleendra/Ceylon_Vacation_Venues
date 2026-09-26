<?php

namespace App\Http\Requests\Admin\Blog;

use App\Models\BlogCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BlogCategory::class) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('blog_categories', 'slug')], 'description' => ['nullable', 'string', 'max:2000'], 'status' => ['required', Rule::in(['draft', 'published'])], 'sort_order' => ['required', 'integer', 'min:0']];
    }
}
