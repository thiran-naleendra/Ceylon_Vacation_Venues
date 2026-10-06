<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Page;
use App\Services\AllowedHtmlSanitizer;
use App\Services\PublicSiteData;
use App\Services\SeoManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $page = Page::query()->where('page_key', 'blog')->published()->with(['sections', 'seoMetadata'])->first();
        if ($page === null) {
            $page = (new Page)->forceFill(['page_key' => 'blog', 'title' => 'Blog', 'summary' => 'Read destination ideas and practical guidance for planning your Sri Lankan journey.']);
            $page->setRelation('sections', collect());
            $page->setRelation('seoMetadata', null);
        }
        $hero = $page->sections->firstWhere('section_key', 'hero')?->content ?? [];
        $image = data_get($hero, 'image.path') ? Storage::disk(data_get($hero, 'image.disk', 'public'))->url(data_get($hero, 'image.path')) : null;
        $url = route('blog.index');
        $seo = $this->seo->make($page, $url, $page->title, $page->summary, $image, [['name' => 'Home', 'url' => url('/')], ['name' => $page->title, 'url' => $url]]);

        return view('public.blog.index', compact('posts', 'featuredPost', 'categories', 'filters', 'page', 'hero', 'seo'));
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
