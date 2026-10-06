<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\Page;
use App\Services\SeoManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function __construct(private readonly SeoManager $seo) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii'],
        ]);
        $images = GalleryImage::query()
            ->visible()
            ->published()
            ->whereHas('album', fn ($query) => $query->published())
            ->with('album:id,title,slug')
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->whereHas('album', fn ($query) => $query->where('slug', $category)))
            ->ordered()
            ->paginate(18)
            ->withQueryString();
        $albums = GalleryAlbum::query()->published()->withCount(['images' => fn ($query) => $query->visible()->published()])->ordered()->get();
        $page = Page::query()->where('page_key', 'gallery')->published()->with(['sections', 'seoMetadata'])->first();
        if ($page === null) {
            $page = (new Page)->forceFill(['page_key' => 'gallery', 'title' => 'Gallery', 'summary' => 'Explore moments from destinations and journeys across Sri Lanka.']);
            $page->setRelation('sections', collect());
            $page->setRelation('seoMetadata', null);
        }
        $hero = $page->sections->firstWhere('section_key', 'hero')?->content ?? [];
        $image = data_get($hero, 'image.path') ? Storage::disk(data_get($hero, 'image.disk', 'public'))->url(data_get($hero, 'image.path')) : null;
        $url = route('gallery.index');
        $seo = $this->seo->make($page, $url, $page->title, $page->summary, $image, [['name' => 'Home', 'url' => url('/')], ['name' => $page->title, 'url' => $url]]);

        return view('public.gallery.index', compact('images', 'albums', 'filters', 'page', 'hero', 'seo'));
    }
}
