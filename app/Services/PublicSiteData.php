<?php

namespace App\Services;

use App\Models\Page;
use App\Models\SocialLink;
use App\Models\WebsiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PublicSiteData
{
    /** @var array{settings: Collection<string, mixed>, socialLinks: Collection<int, SocialLink>, pages: Collection<string, array{title:string,url:string}>}|null */
    private ?array $resolved = null;

    /** @return array{settings: Collection<string, mixed>, socialLinks: Collection<int, SocialLink>, pages: Collection<string, array{title:string,url:string}>} */
    public function get(): array
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        if (! Schema::hasTable('website_settings')) {
            return $this->resolved = ['settings' => collect(), 'socialLinks' => collect(), 'pages' => collect()];
        }

        $settings = WebsiteSetting::query()->get(['key', 'value'])->pluck('value', 'key');
        $settings->put('business.name', $settings->get('business.name') ?: 'Ceylon Vacation Venues');

        foreach (['branding.logo_path', 'branding.favicon_path'] as $key) {
            if (is_string($settings->get($key)) && $settings->get($key) !== '') {
                $settings->put(str_replace('_path', '_url', $key), Storage::disk('public')->url($settings->get($key)));
            }
        }

        $socialLinks = Schema::hasTable('social_links')
            ? SocialLink::query()->active()->ordered()->get()
            : collect();

        $pages = Schema::hasTable('pages')
            ? Page::query()->published()->whereIn('page_key', ['about', 'visa-extension', 'baggage-transport', 'privacy-policy', 'terms-and-conditions'])
                ->get(['page_key', 'title', 'slug'])
                ->mapWithKeys(fn (Page $page): array => [$page->page_key => ['title' => $page->title, 'url' => $this->pageUrl($page)]])
            : collect();

        return $this->resolved = compact('settings', 'socialLinks', 'pages');
    }

    private function pageUrl(Page $page): string
    {
        return match ($page->page_key) {
            'about' => route('about'),
            'visa-extension' => route('services.visa'),
            'baggage-transport' => route('services.baggage'),
            'privacy-policy' => route('privacy'),
            'terms-and-conditions' => route('terms'),
            default => route('pages.show', $page),
        };
    }
}
