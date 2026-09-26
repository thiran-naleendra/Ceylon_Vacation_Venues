<?php

namespace App\Http\Controllers;

use App\Http\Requests\Public\IndexTourPackageRequest;
use App\Http\Requests\Public\IndexVehicleRequest;
use App\Models\BlogPost;
use App\Models\GalleryImage;
use App\Models\Page;
use App\Models\TourPackage;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Services\PublicSiteData;
use App\Services\SeoManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function __construct(private readonly SeoManager $seo, private readonly PublicSiteData $siteData) {}

    public function home(): View
    {
        if (! Schema::hasTable('pages')) {
            return view('welcome');
        }

        $page = Page::where('page_key', 'home')->published()->with(['sections', 'seoMetadata'])->first();
        if (! $page) {
            $page = (new Page)->forceFill([
                'page_key' => 'home',
                'title' => 'Ceylon Vacation Venues',
                'summary' => 'Thoughtful tours and practical travel services for a smooth journey across Sri Lanka.',
            ]);
            $page->setRelation('sections', collect());
            $page->setRelation('seoMetadata', null);
        }

        $page->loadMissing(['sections', 'seoMetadata']);
        $featuredPackages = TourPackage::query()->published()->featured()->with('featuredImage')->ordered()->limit(3)->get();
        $featuredVehicles = Vehicle::query()->published()->featured()->with(['category', 'featuredImage'])->ordered()->limit(3)->get();
        $galleryImages = GalleryImage::query()->visible()->published()->whereHas('album', fn ($query) => $query->published())->with('album:id,title,slug')->ordered()->limit(6)->get();
        $latestPosts = BlogPost::query()->published()->with(['category:id,name,slug', 'author:id,name'])->latest('published_at')->limit(3)->get();
        $destinations = TourPackage::query()->published()->whereNotNull('destination')->where('destination', '!=', '')->ordered()->limit(8)->pluck('destination')->unique()->values();
        $hero = $page->sections->firstWhere('section_key', 'hero')?->content ?? [];
        $image = data_get($hero, 'image.path') ? Storage::disk(data_get($hero, 'image.disk', 'public'))->url(data_get($hero, 'image.path')) : null;
        $seo = $this->seo->make($page, url('/'), $page->title, $page->summary, $image, [['name' => 'Home', 'url' => url('/')]], [$this->seo->organization(), $this->seo->website()]);

        return view('public.home', compact('page', 'hero', 'seo', 'featuredPackages', 'featuredVehicles', 'galleryImages', 'latestPosts', 'destinations'));
    }

    public function fixedPage(Request $request): View
    {
        $pageKey = (string) $request->route('page_key');
        $page = Page::query()->where('page_key', $pageKey)->published()->with(['sections', 'seoMetadata'])->firstOrFail();
        $url = route((string) $request->route('canonical_route'));
        $schema = $this->seo->pageSchema($page, $url);

        return $this->pageView($page, $url, array_filter([$schema]));
    }

    public function page(Page $page): View|RedirectResponse
    {
        abort_unless($page->isPublished(), 404);
        $preferredRoute = $this->preferredPageRoute($page->page_key);
        if ($preferredRoute !== null) {
            return redirect()->route($preferredRoute, status: 301);
        }

        $page->load(['sections', 'seoMetadata']);
        $schema = $this->seo->pageSchema($page, route('pages.show', $page));

        return $this->pageView($page, route('pages.show', $page), array_filter([$schema]));
    }

    public function packages(IndexTourPackageRequest $request): View
    {
        $filters = $request->validated();
        $packages = TourPackage::query()
            ->published()
            ->with('featuredImage')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(fn ($query) => $query->where('title', 'like', "%{$search}%")->orWhere('summary', 'like', "%{$search}%")->orWhere('destination', 'like', "%{$search}%")))
            ->when($filters['destination'] ?? null, fn ($query, string $destination) => $query->where('destination', $destination))
            ->when($filters['availability'] ?? null, fn ($query, string $availability) => $query->where('availability_status', $availability))
            ->when(($filters['duration'] ?? null) === '1-3', fn ($query) => $query->whereBetween('duration_days', [1, 3]))
            ->when(($filters['duration'] ?? null) === '4-7', fn ($query) => $query->whereBetween('duration_days', [4, 7]))
            ->when(($filters['duration'] ?? null) === '8+', fn ($query) => $query->where('duration_days', '>=', 8));
        $this->sortPackages($packages, $filters['sort'] ?? 'recommended');
        $packages = $packages->paginate(12)->withQueryString();
        $destinations = TourPackage::query()->published()->whereNotNull('destination')->where('destination', '!=', '')->distinct()->orderBy('destination')->pluck('destination');
        $schema = $this->seo->breadcrumbs([['name' => 'Home', 'url' => url('/')], ['name' => 'Tour Packages', 'url' => route('packages.index')]]);

        return view('public.packages.index', compact('packages', 'destinations', 'filters', 'schema'));
    }

    public function package(TourPackage $package): View
    {
        abort_unless($package->isPublished(), 404);
        $package->load(['itineraries' => fn ($query) => $query->ordered(), 'images' => fn ($query) => $query->ready()->ordered(), 'seoMetadata']);
        $relatedPackages = TourPackage::query()->published()->whereKeyNot($package->id)
            ->when($package->destination, fn ($query, string $destination) => $query->where('destination', $destination))
            ->with('featuredImage')->ordered()->limit(3)->get();
        $url = route('packages.show', $package);
        $image = $package->images->first()?->url();
        $breadcrumbs = [['name' => 'Home', 'url' => url('/')], ['name' => 'Tour Packages', 'url' => route('packages.index')], ['name' => $package->title, 'url' => $url]];
        $seo = $this->seo->make($package, $url, $package->title, $package->summary, $image, $breadcrumbs, [$this->seo->packageSchema($package, $url)]);
        $whatsAppUrl = $this->whatsAppUrl('Hello, I would like to ask about the '.$package->title.' tour package: '.$url);

        return view('public.packages.show', compact('package', 'relatedPackages', 'seo', 'breadcrumbs', 'whatsAppUrl'));
    }

    public function vehicles(IndexVehicleRequest $request): View
    {
        $filters = $request->validated();
        $vehicles = Vehicle::query()
            ->published()
            ->whereHas('category', fn ($query) => $query->published())
            ->with(['category:id,name,slug', 'featuredImage'])
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(fn ($query) => $query->where('title', 'like', "%{$search}%")->orWhere('summary', 'like', "%{$search}%")->orWhere('make', 'like', "%{$search}%")->orWhere('model', 'like', "%{$search}%")))
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->whereHas('category', fn ($query) => $query->where('slug', $category)))
            ->when($filters['availability'] ?? null, fn ($query, string $availability) => $query->where('availability_status', $availability))
            ->when($filters['transmission'] ?? null, fn ($query, string $transmission) => $query->where('transmission', $transmission))
            ->when($filters['passengers'] ?? null, fn ($query, int $passengers) => $query->where('seats', '>=', $passengers));
        $this->sortVehicles($vehicles, $filters['sort'] ?? 'recommended');
        $vehicles = $vehicles->paginate(12)->withQueryString();
        $categories = VehicleCategory::query()->published()->ordered()->get(['id', 'name', 'slug']);
        $transmissions = Vehicle::query()->published()->whereNotNull('transmission')->distinct()->orderBy('transmission')->pluck('transmission');
        $schema = $this->seo->breadcrumbs([['name' => 'Home', 'url' => url('/')], ['name' => 'Vehicle Rental', 'url' => route('vehicles.index')]]);

        return view('public.vehicles.index', compact('vehicles', 'categories', 'transmissions', 'filters', 'schema'));
    }

    public function vehicle(Vehicle $vehicle): View
    {
        $vehicle->loadMissing('category');
        abort_unless($vehicle->isPublished() && $vehicle->category?->isPublished(), 404);
        $vehicle->load(['images' => fn ($query) => $query->ready()->ordered(), 'seoMetadata']);
        $relatedVehicles = Vehicle::query()->published()->whereKeyNot($vehicle->id)->where('vehicle_category_id', $vehicle->vehicle_category_id)
            ->whereHas('category', fn ($query) => $query->published())->with(['category:id,name,slug', 'featuredImage'])->ordered()->limit(3)->get();
        $url = route('vehicles.show', $vehicle);
        $image = $vehicle->images->first()?->url();
        $breadcrumbs = [['name' => 'Home', 'url' => url('/')], ['name' => 'Vehicle Rental', 'url' => route('vehicles.index')], ['name' => $vehicle->title, 'url' => $url]];
        $seo = $this->seo->make($vehicle, $url, $vehicle->title, $vehicle->summary, $image, $breadcrumbs, [$this->seo->vehicleSchema($vehicle, $url)]);
        $whatsAppUrl = $this->whatsAppUrl('Hello, I would like to ask about renting the '.$vehicle->title.': '.$url);

        return view('public.vehicles.show', compact('vehicle', 'relatedVehicles', 'seo', 'breadcrumbs', 'whatsAppUrl'));
    }

    private function sortPackages(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_low' => $query->orderByRaw('starting_price is null')->orderBy('starting_price'),
            'price_high' => $query->orderByRaw('starting_price is null')->orderByDesc('starting_price'),
            'duration_short' => $query->orderBy('duration_days'),
            'duration_long' => $query->orderByDesc('duration_days'),
            default => $query->ordered(),
        };
    }

    private function sortVehicles(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_low' => $query->orderByRaw('rental_rate is null')->orderBy('rental_rate'),
            'price_high' => $query->orderByRaw('rental_rate is null')->orderByDesc('rental_rate'),
            'capacity' => $query->orderByDesc('seats'),
            default => $query->ordered(),
        };
    }

    private function whatsAppUrl(string $message): ?string
    {
        $number = $this->siteData->get()['settings']->get('contact.whatsapp_number');
        $digits = is_string($number) ? preg_replace('/\D+/', '', $number) : null;

        return $digits ? 'https://wa.me/'.$digits.'?text='.rawurlencode($message) : null;
    }

    private function preferredPageRoute(string $pageKey): ?string
    {
        return match ($pageKey) {
            'about' => 'about',
            'visa-extension' => 'services.visa',
            'baggage-transport' => 'services.baggage',
            'contact' => 'inquiries.contact.create',
            'privacy-policy' => 'privacy',
            'terms-and-conditions' => 'terms',
            default => null,
        };
    }

    private function pageView(Page $page, string $url, array $schemas): View
    {
        $hero = $page->sections->firstWhere('section_key', 'hero')?->content ?? [];
        $breadcrumbs = [['name' => 'Home', 'url' => url('/')]];
        if ($page->page_key !== 'home') {
            $breadcrumbs[] = ['name' => $page->title, 'url' => $url];
        }
        $image = data_get($hero, 'image.path') ? Storage::disk(data_get($hero, 'image.disk', 'public'))->url(data_get($hero, 'image.path')) : null;
        $seo = $this->seo->make($page, $url, $page->title, $page->summary, $image, $breadcrumbs, $schemas);

        return view('public.pages.show', compact('page', 'hero', 'seo', 'breadcrumbs'));
    }
}
