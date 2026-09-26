<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Blog\StoreBlogPostRequest;
use App\Http\Requests\Admin\Blog\UpdateBlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\AllowedHtmlSanitizer;
use App\Services\BlogImageService;
use App\Services\RedirectManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function __construct(private readonly AllowedHtmlSanitizer $sanitizer, private readonly BlogImageService $images, private readonly RedirectManager $redirects) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', BlogPost::class);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:120'], 'category' => ['nullable', 'integer', 'exists:blog_categories,id'], 'status' => ['nullable', 'in:draft,published'], 'featured' => ['nullable', 'boolean']]);
        $posts = BlogPost::with(['category:id,name', 'author:id,name'])->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('title', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%")))->when($filters['category'] ?? null, fn ($q, $v) => $q->where('blog_category_id', $v))->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when(array_key_exists('featured', $filters), fn ($q) => $q->where('is_featured', $request->boolean('featured')))->latest('published_at')->latest('id')->paginate(15)->withQueryString();

        return view('admin.blog.index', ['posts' => $posts, 'filters' => $filters, 'categories' => BlogCategory::ordered()->get()]);
    }

    public function create(): View
    {
        Gate::authorize('create', BlogPost::class);

        return $this->form(new BlogPost, 'admin.blog.create');
    }

    public function store(StoreBlogPostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $image = $request->hasFile('featured_image') ? $this->images->store($request->file('featured_image')) : null;
        $post = BlogPost::create($this->attributes($data));
        $post->forceFill(['author_id' => $request->user()->id, 'featured_image_path' => $image['path'] ?? null, 'featured_image_variants' => $image['variants'] ?? null]);
        $this->setPublication($post, $data);
        $this->syncSeo($post, $data, $request->boolean('seo_use_featured_image'));

        return redirect()->route('admin.blog.show', $post)->with('success', 'Blog post created.');
    }

    public function show(BlogPost $post): View
    {
        Gate::authorize('view', $post);
        $post->load(['category', 'author', 'seoMetadata']);

        return view('admin.blog.show', compact('post'));
    }

    public function edit(BlogPost $post): View
    {
        Gate::authorize('update', $post);
        $post->load('seoMetadata');

        return $this->form($post, 'admin.blog.edit');
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $post): RedirectResponse
    {
        $data = $request->validated();
        $old = $post->featured_image_path;
        $oldVariants = $post->featured_image_variants;
        $oldSlug = $post->slug;
        $new = $request->hasFile('featured_image') ? $this->images->store($request->file('featured_image')) : null;
        $post->update($this->attributes($data));
        $this->redirects->record('/blog/'.$oldSlug, '/blog/'.$post->slug);
        if ($new || $request->boolean('remove_featured_image')) {
            $post->forceFill(['featured_image_path' => $new['path'] ?? null, 'featured_image_variants' => $new['variants'] ?? null])->save();
            $this->images->delete($old, $oldVariants);
        } $this->setPublication($post, $data);
        $this->syncSeo($post, $data, $request->boolean('seo_use_featured_image'));

        return redirect()->route('admin.blog.show', $post)->with('success', 'Blog post updated.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        Gate::authorize('delete', $post);
        $path = $post->featured_image_path;
        $variants = $post->featured_image_variants;
        $post->delete();
        $this->images->delete($path, $variants);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted.');
    }

    private function form(BlogPost $post, string $view): View
    {
        return view($view, ['post' => $post, 'categories' => BlogCategory::ordered()->get()]);
    }

    private function attributes(array $data): array
    {
        $values = Arr::only($data, ['blog_category_id', 'title', 'slug', 'excerpt', 'featured_image_alt']);
        if (blank($values['slug'] ?? null)) {
            unset($values['slug']);
        } $values['body'] = $this->sanitizer->sanitize($data['body']);

        return $values;
    }

    private function setPublication(BlogPost $post, array $data): void
    {
        $status = PublicationStatus::from($data['status']);
        $post->forceFill(['status' => $status, 'published_at' => $status === PublicationStatus::Published ? ($data['published_at'] ?? $post->published_at ?? now()) : null, 'is_featured' => (bool) $data['is_featured']])->save();
    }

    private function syncSeo(BlogPost $post, array $data, bool $useImage): void
    {
        $seo = $post->seoMetadata()->firstOrNew();
        $seo->fill($data['seo'] ?? []);
        $seo->forceFill(['og_image_path' => $useImage ? $post->featured_image_path : null, 'og_image_alt' => data_get($data, 'seo.og_image_alt') ?: ($useImage ? $post->featured_image_alt : null)])->save();
    }
}
