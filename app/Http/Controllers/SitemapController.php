<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Property;
use App\Models\TourPackage;
use App\Models\Vehicle;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $entries = Cache::remember('public.sitemap.entries.v2', now()->addHour(), function (): array {
            $indexable = fn ($query) => $query->whereDoesntHave('seoMetadata', fn ($seo) => $seo->where('robots_index', false));

            return [
                'pages' => Page::published()->where($indexable)->get(['id', 'page_key', 'slug', 'updated_at'])->toArray(),
                'packages' => TourPackage::published()->where($indexable)->get(['id', 'slug', 'updated_at'])->toArray(),
                'vehicles' => Vehicle::published()->where($indexable)->get(['id', 'slug', 'updated_at'])->toArray(),
                'properties' => Property::published()->where($indexable)->get(['id', 'slug', 'updated_at'])->toArray(),
                'posts' => BlogPost::published()->where($indexable)->get(['id', 'slug', 'updated_at'])->toArray(),
            ];
        });

        $urls = collect($entries['pages'])->map(fn (array $page): array => ['loc' => $this->pageUrl((new Page)->forceFill($page)), 'lastmod' => Carbon::parse($page['updated_at'])]);
        foreach (['packages' => ['packages.show', TourPackage::class], 'vehicles' => ['vehicles.show', Vehicle::class], 'properties' => ['properties.show', Property::class], 'posts' => ['blog.show', BlogPost::class]] as $key => [$route, $model]) {
            foreach ($entries[$key] as $entry) {
                $urls->push(['loc' => route($route, (new $model)->forceFill($entry)), 'lastmod' => Carbon::parse($entry['updated_at'])]);
            }
        }

        return response()->view('public.sitemap', compact('urls'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function pageUrl(Page $page): string
    {
        return match ($page->page_key) {
            'home' => url('/'),
            'about' => route('about'),
            'visa-extension' => route('services.visa'),
            'baggage-transport' => route('services.baggage'),
            'contact' => route('inquiries.contact.create'),
            'privacy-policy' => route('privacy'),
            'terms-and-conditions' => route('terms'),
            default => route('pages.show', $page),
        };
    }
}
