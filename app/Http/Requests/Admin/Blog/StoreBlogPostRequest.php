<?php

namespace App\Http\Requests\Admin\Blog;

use App\Models\BlogPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BlogPost::class) ?? false;
    }

    public function rules(): array
    {
        return ['blog_category_id' => ['required', 'integer', Rule::exists('blog_categories', 'id')], 'title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('blog_posts', 'slug')], 'excerpt' => ['nullable', 'string', 'max:500'], 'body' => ['required', 'string', 'max:200000'], 'featured_image' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('8mb')->dimensions(Rule::dimensions()->minWidth(800)->minHeight(400)->maxWidth(8000)->maxHeight(8000))], 'featured_image_alt' => ['nullable', 'string', 'max:255', 'required_with:featured_image'], 'remove_featured_image' => ['nullable', 'boolean'], 'status' => ['required', Rule::in(['draft', 'published'])], 'published_at' => ['nullable', 'date'], 'is_featured' => ['required', 'boolean'], 'seo.meta_title' => ['nullable', 'string', 'max:255'], 'seo.meta_description' => ['nullable', 'string', 'max:500'], 'seo.canonical_url' => ['nullable', 'url:https', 'max:2048'], 'seo.robots_index' => ['required', 'boolean'], 'seo.robots_follow' => ['required', 'boolean'], 'seo.og_title' => ['nullable', 'string', 'max:255'], 'seo.og_description' => ['nullable', 'string', 'max:500'], 'seo.og_image_alt' => ['nullable', 'string', 'max:255'], 'seo_use_featured_image' => ['nullable', 'boolean']];
    }
}
