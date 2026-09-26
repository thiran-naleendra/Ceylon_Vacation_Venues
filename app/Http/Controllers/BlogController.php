<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\AllowedHtmlSanitizer;
use App\Services\PublicSiteData;
use App\Services\SeoManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(private readonly SeoManager $seo, private readonly PublicSiteData $siteData, private readonly AllowedHtmlSanitizer $sanitizer) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii'],
        ]);
        $baseQuery = fn () => BlogPost::query()
            ->published()
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->whereHas('category', fn ($query) => $query->where('slug', $category)));
        $featuredPost = $baseQuery()->featured()->with('category:id,name,slug')->latest('published_at')->first();
        $posts = $baseQuery()
            ->when($featuredPost, fn ($query) => $query->whereKeyNot($featuredPost->id))
            ->with('category:id,name,slug')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();
        $categories = BlogCategory::query()->published()->select(['id', 'name', 'slug'])->withCount(['posts' => fn ($query) => $query->published()])->ordered()->get();
        $schema = $this->seo->breadcrumbs([['name' => 'Home', 'url' => url('/')], ['name' => 'Blog', 'url' => route('blog.index')]]);

        return view('public.blog.index', compact('posts', 'featuredPost', 'categories', 'filters', 'schema'));
    }

    public function show(BlogPost $post): View
    {
        $post->loadMissing('category');
        abort_unless($post->isPublished(), 404);
        $post->load(['author', 'seoMetadata']);
        $related = BlogPost::query()->published()->where('blog_category_id', $post->blog_category_id)->whereKeyNot($post->id)
            ->with('category:id,name,slug')->latest('published_at')->limit(3)->get();
        $businessName = $this->siteData->get()['settings']->get('business.name', 'Ceylon Vacation Venues');
        $url = route('blog.show', $post);
        $breadcrumbs = [['name' => 'Home', 'url' => url('/')], ['name' => 'Blog', 'url' => route('blog.index')], ['name' => $post->title, 'url' => $url]];
        $seo = $this->seo->make($post, $url, $post->title, $post->excerpt, $post->featuredImageUrl(), $breadcrumbs, [$this->seo->articleSchema($post, $url)]);
        $safeBody = $this->sanitizer->sanitize($post->body);

        return view('public.blog.show', compact('post', 'related', 'businessName', 'safeBody', 'seo'));
    }
}
