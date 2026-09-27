<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Property;
use App\Models\PropertyType;
use App\Services\PublicSiteData;
use App\Services\SeoManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function __construct(private readonly SeoManager $seo, private readonly PublicSiteData $siteData) {}

    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'type' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii'], 'location' => ['nullable', 'string', 'max:255'], 'guests' => ['nullable', 'integer', 'min:1', 'max:500']]);
        $properties = Property::query()->published()->whereHas('type', fn ($q) => $q->published())->with(['type:id,name,slug', 'featuredImage'])->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('short_description', 'like', "%{$s}%")->orWhere('location', 'like', "%{$s}%")))->when($filters['type'] ?? null, fn ($q, $v) => $q->whereHas('type', fn ($q) => $q->where('slug', $v)))->when($filters['location'] ?? null, fn ($q, $v) => $q->where('location', $v))->when($filters['guests'] ?? null, fn ($q, $v) => $q->where('max_guests', '>=', $v))->ordered()->paginate(12)->withQueryString();
        $types = PropertyType::published()->ordered()->get(['id', 'name', 'slug']);
        $locations = Property::published()->whereNotNull('location')->distinct()->orderBy('location')->pluck('location');
        $page = Page::query()->where('page_key', 'villas-houses')->published()->with(['sections', 'seoMetadata'])->first();

        if ($page === null) {
            $page = (new Page)->forceFill([
                'page_key' => 'villas-houses',
                'title' => 'Villas & Houses',
                'summary' => 'Find a comfortable base for your Sri Lankan holiday and contact our team to arrange your stay.',
            ]);
            $page->setRelation('sections', collect());
            $page->setRelation('seoMetadata', null);
        }

        $hero = $page->sections->firstWhere('section_key', 'hero')?->content ?? [];
        $heroImage = data_get($hero, 'image');
        $image = data_get($heroImage, 'path')
            ? Storage::disk(data_get($heroImage, 'disk', 'public'))->url(data_get($heroImage, 'path'))
            : null;
        $url = route('properties.index');
        $breadcrumbs = [['name' => 'Home', 'url' => url('/')], ['name' => $page->title, 'url' => $url]];
        $seo = $this->seo->make($page, $url, $page->title, $page->summary, $image, $breadcrumbs);

        return view('public.properties.index', compact('properties', 'types', 'locations', 'filters', 'page', 'hero', 'seo'));
    }

    public function show(Property $property): View
    {
        $property->loadMissing('type');
        abort_unless($property->isPublished() && $property->type?->isPublished(), 404);
        $property->load(['amenities' => fn ($q) => $q->where('is_active', true)->ordered(), 'images' => fn ($q) => $q->ready()->ordered(), 'seoMetadata']);
        $relatedProperties = Property::published()->whereKeyNot($property->id)->where('property_type_id', $property->property_type_id)->whereHas('type', fn ($q) => $q->published())->with(['type:id,name,slug', 'featuredImage'])->ordered()->limit(3)->get();
        $url = route('properties.show', $property);
        $breadcrumbs = [['name' => 'Home', 'url' => url('/')], ['name' => 'Villas & Houses', 'url' => route('properties.index')], ['name' => $property->name, 'url' => $url]];
        $seo = $this->seo->make($property, $url, $property->name, $property->short_description, $property->images->first()?->url(), $breadcrumbs, [$this->seo->propertySchema($property, $url)]);
        $number = $this->siteData->get()['settings']->get('contact.whatsapp_number');
        $digits = is_string($number) ? preg_replace('/\D+/', '', $number) : null;
        $whatsAppUrl = $digits ? 'https://wa.me/'.$digits.'?text='.rawurlencode('Hello, I would like to ask about '.$property->name.': '.$url) : null;

        return view('public.properties.show', compact('property', 'relatedProperties', 'breadcrumbs', 'seo', 'whatsAppUrl'));
    }
}
