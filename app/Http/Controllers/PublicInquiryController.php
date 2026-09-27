<?php

namespace App\Http\Controllers;

use App\Enums\InquiryType;
use App\Http\Requests\Public\StoreInquiryRequest;
use App\Models\Page;
use App\Models\Property;
use App\Models\TourPackage;
use App\Models\Vehicle;
use App\Models\WebsiteSetting;
use App\Services\InquirySubmissionService;
use App\Services\PublicSiteData;
use App\Services\SeoManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicInquiryController extends Controller
{
    public function __construct(
        private readonly InquirySubmissionService $submissions,
        private readonly SeoManager $seo,
        private readonly PublicSiteData $siteData,
    ) {}

    public function package(Request $request, TourPackage $package): View
    {
        abort_unless($package->isPublished(), 404);

        return $this->form($request, InquiryType::Package, $package);
    }

    public function rental(Request $request, Vehicle $vehicle): View
    {
        $this->ensureVehicleIsPublic($vehicle);

        return $this->form($request, InquiryType::Rental, $vehicle);
    }

    public function property(Request $request, Property $property): View
    {
        $this->ensurePropertyIsPublic($property);

        return $this->form($request, InquiryType::Property, $property);
    }

    public function visaPage(Request $request): View
    {
        return $this->servicePage($request, InquiryType::Visa, 'visa-extension', 'Visa Assistance', 'Coming to Sri Lanka, extending your stay or planning to do business here? We can help with your visa assistance needs.');
    }

    public function visa(Request $request): View
    {
        return $this->form($request, InquiryType::Visa);
    }

    public function baggagePage(Request $request): View
    {
        return $this->servicePage($request, InquiryType::Baggage, 'baggage-transport', 'Airport Baggage Recovery', 'Missed your baggage at the airport? No worries—we will bring it back to you, so you do not have to waste time retrieving it.');
    }

    public function baggage(Request $request): View
    {
        return $this->form($request, InquiryType::Baggage);
    }

    public function contact(Request $request): View
    {
        $page = $this->contentPage('contact', 'Contact Us', 'Contact our team about tours and travel services in Sri Lanka.');
        $token = $this->formToken($request);
        $siteData = $this->siteData->get();
        $settings = $siteData['settings'];
        $socialLinks = $siteData['socialLinks'];
        $url = route('inquiries.contact.create');
        $hero = $page->sections->firstWhere('section_key', 'hero')?->content ?? [];
        $image = data_get($hero, 'image.path') ? Storage::disk(data_get($hero, 'image.disk', 'public'))->url(data_get($hero, 'image.path')) : null;
        $seo = $this->seo->make($page, $url, $page->title, $page->summary, $image, [['name' => 'Home', 'url' => url('/')], ['name' => 'Contact', 'url' => $url]]);

        return view('public.contact', compact('page', 'hero', 'token', 'settings', 'socialLinks', 'seo'));
    }

    public function storePackage(StoreInquiryRequest $request, TourPackage $package): RedirectResponse
    {
        return $this->store($request, $package);
    }

    public function storeRental(StoreInquiryRequest $request, Vehicle $vehicle): RedirectResponse
    {
        return $this->store($request, $vehicle);
    }

    public function storeProperty(StoreInquiryRequest $request, Property $property): RedirectResponse
    {
        return $this->store($request, $property);
    }

    public function storeVisa(StoreInquiryRequest $request): RedirectResponse
    {
        return $this->store($request);
    }

    public function storeBaggage(StoreInquiryRequest $request): RedirectResponse
    {
        return $this->store($request);
    }

    public function storeContact(StoreInquiryRequest $request): RedirectResponse
    {
        return $this->store($request);
    }

    private function store(StoreInquiryRequest $request, TourPackage|Vehicle|Property|null $subject = null): RedirectResponse
    {
        if ($subject instanceof Vehicle) {
            $this->ensureVehicleIsPublic($subject);
        } elseif ($subject instanceof Property) {
            $this->ensurePropertyIsPublic($subject);
        } elseif ($subject instanceof TourPackage) {
            abort_unless($subject->isPublished(), 404);
        }

        $inquiry = $this->submissions->submit($request->inquiryType(), $request->validated(), $subject);
        $request->session()->forget('inquiry_form_tokens.'.$request->string('form_token')->toString());

        return back()->with('inquiry_success', $inquiry->reference);
    }

    private function servicePage(Request $request, InquiryType $type, string $pageKey, string $title, string $summary): View
    {
        $page = $this->contentPage($pageKey, $title, $summary);
        $token = $this->formToken($request);
        $settings = $this->siteData->get()['settings'];
        $whatsApp = $settings->get('contact.whatsapp_number');
        $routeName = $type === InquiryType::Visa ? 'services.visa' : 'services.baggage';
        $url = route($routeName);
        $hero = $page->sections->firstWhere('section_key', 'hero')?->content ?? [];
        $image = data_get($hero, 'image.path') ? Storage::disk(data_get($hero, 'image.disk', 'public'))->url(data_get($hero, 'image.path')) : null;
        $schema = $this->seo->pageSchema($page, $url) ?? ['@context' => 'https://schema.org', '@type' => 'Service', 'name' => $page->title, 'description' => $page->summary, 'url' => $url];
        $seo = $this->seo->make($page, $url, $page->title, $page->summary, $image, [['name' => 'Home', 'url' => url('/')], ['name' => $page->title, 'url' => $url]], [$schema]);

        return view('public.services.show', compact('page', 'hero', 'type', 'token', 'settings', 'whatsApp', 'seo'));
    }

    private function contentPage(string $pageKey, string $title, string $summary): Page
    {
        $page = Page::query()->where('page_key', $pageKey)->published()->with(['sections', 'seoMetadata'])->first();
        if ($page !== null) {
            return $page;
        }

        $page = (new Page)->forceFill(['page_key' => $pageKey, 'title' => $title, 'summary' => $summary]);
        $page->setRelation('sections', collect());
        $page->setRelation('seoMetadata', null);

        return $page;
    }

    private function formToken(Request $request): string
    {
        $token = (string) Str::uuid();
        $tokens = $request->session()->get('inquiry_form_tokens', []);
        $tokens[$token] = now()->timestamp;
        $request->session()->put('inquiry_form_tokens', array_slice($tokens, -10, null, true));

        return $token;
    }

    private function form(Request $request, InquiryType $type, TourPackage|Vehicle|Property|null $subject = null): View
    {
        $token = $this->formToken($request);
        $whatsApp = WebsiteSetting::query()->where('key', 'contact.whatsapp_number')->first()?->value;

        return view('public.inquiries.form', compact('type', 'subject', 'token', 'whatsApp'));
    }

    private function ensureVehicleIsPublic(Vehicle $vehicle): void
    {
        $vehicle->loadMissing('category');
        abort_unless($vehicle->isPublished() && $vehicle->category?->isPublished(), 404);
    }

    private function ensurePropertyIsPublic(Property $property): void
    {
        $property->loadMissing('type');
        abort_unless($property->isPublished() && $property->type?->isPublished(), 404);
    }
}
