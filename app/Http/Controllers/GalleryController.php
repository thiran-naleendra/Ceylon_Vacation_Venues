<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Services\SeoManager;
use Illuminate\Http\Request;
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
        $schema = $this->seo->breadcrumbs([['name' => 'Home', 'url' => url('/')], ['name' => 'Gallery', 'url' => route('gallery.index')]]);

        return view('public.gallery.index', compact('images', 'albums', 'filters', 'schema'));
    }
}
