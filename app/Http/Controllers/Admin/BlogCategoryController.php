<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\StoreBlogCategoryRequest;
use App\Http\Requests\Admin\Blog\UpdateBlogCategoryRequest;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', BlogCategory::class);

        return view('admin.blog-categories.index', ['categories' => BlogCategory::withCount('posts')->ordered()->get()]);
    }

    public function create(): View
    {
        Gate::authorize('create', BlogCategory::class);

        return view('admin.blog-categories.form', ['category' => new BlogCategory]);
    }

    public function store(StoreBlogCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $status = PublicationStatus::from($data['status']);
        unset($data['status']);
        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        } $category = BlogCategory::create($data);
        $category->forceFill(['status' => $status, 'published_at' => $status === PublicationStatus::Published ? now() : null])->save();

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category created.');
    }

    public function edit(BlogCategory $blogCategory): View
    {
        Gate::authorize('update', $blogCategory);

        return view('admin.blog-categories.form', ['category' => $blogCategory]);
    }

    public function update(UpdateBlogCategoryRequest $request, BlogCategory $blogCategory): RedirectResponse
    {
        $data = $request->validated();
        $status = PublicationStatus::from($data['status']);
        unset($data['status']);
        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        } $blogCategory->update($data);
        $blogCategory->forceFill(['status' => $status, 'published_at' => $status === PublicationStatus::Published ? ($blogCategory->published_at ?? now()) : null])->save();

        return back()->with('success', 'Blog category updated.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        Gate::authorize('delete', $blogCategory);
        if ($blogCategory->posts()->exists()) {
            return back()->withErrors(['category' => 'Categories containing posts cannot be deleted.']);
        } $blogCategory->delete();

        return back()->with('success', 'Blog category deleted.');
    }
}
