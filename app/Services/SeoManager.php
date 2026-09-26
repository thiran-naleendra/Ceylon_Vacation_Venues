<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Property;
use App\Models\TourPackage;
use App\Models\Vehicle;
use App\Models\WebsiteSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeoManager
{
    /** @param array<int, array{name:string,url:string}> $breadcrumbs @param array<int, array<string,mixed>> $schemas */
    public function make(Model $model, string $url, string $fallbackTitle, ?string $fallbackDescription, ?string $fallbackImage, array $breadcrumbs, array $schemas = []): array
    {
        $metadata = $model->seoMetadata;
        $imagePath = $metadata?->og_image_path;
        $image = $imagePath ? Storage::disk('public')->url($imagePath) : $fallbackImage;
        $robots = ($metadata?->robots_index ?? true) ? 'index' : 'noindex';
        $robots .= ($metadata?->robots_follow ?? true) ? ',follow' : ',nofollow';

        return [
            'title' => $metadata?->meta_title ?: $fallbackTitle,
            'description' => $metadata?->meta_description ?: Str::limit(strip_tags((string) $fallbackDescription), 160, ''),
            'canonical' => $metadata?->canonical_url ?: $url,
            'robots' => $robots,
            'og_title' => $metadata?->og_title ?: ($metadata?->meta_title ?: $fallbackTitle),
            'og_description' => $metadata?->og_description ?: ($metadata?->meta_description ?: $fallbackDescription),
            'og_image' => $image,
            'og_image_alt' => $metadata?->og_image_alt,
            'schemas' => [...$schemas, $this->breadcrumbs($breadcrumbs)],
        ];
    }

    /** @param array<int, array{name:string,url:string}> $items */
    public function breadcrumbs(array $items): array
    {
        return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => array_map(fn (array $item, int $index): array => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'], 'item' => $item['url']], $items, array_keys($items))];
    }

    public function organization(): array
    {
        $settings = WebsiteSetting::query()->whereIn('key', ['business.name', 'contact.phone', 'contact.email', 'contact.address', 'branding.logo_path'])->get()->pluck('value', 'key');
        $schema = ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => $settings['business.name'] ?? 'Ceylon Vacation Venues', 'url' => url('/')];
        if ($settings['contact.phone'] ?? null) {
            $schema['telephone'] = $settings['contact.phone'];
        }
        if ($settings['contact.email'] ?? null) {
            $schema['email'] = $settings['contact.email'];
        }
        if ($settings['contact.address'] ?? null) {
            $schema['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $settings['contact.address']];
        }
        if ($settings['branding.logo_path'] ?? null) {
            $schema['logo'] = Storage::disk('public')->url($settings['branding.logo_path']);
        }

        return $schema;
    }

    public function website(): array
    {
        return ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => $this->organization()['name'], 'url' => url('/')];
    }

    public function pageSchema(Page $page, string $url): ?array
    {
        if (! in_array($page->page_key, ['visa-extension', 'baggage-transport'], true)) {
            return null;
        }

        return ['@context' => 'https://schema.org', '@type' => 'Service', 'name' => $page->title, 'description' => $page->summary, 'url' => $url, 'provider' => ['@type' => 'Organization', 'name' => $this->organization()['name']]];
    }

    public function packageSchema(TourPackage $package, string $url): array
    {
        $schema = ['@context' => 'https://schema.org', '@type' => 'TouristTrip', 'name' => $package->title, 'description' => $package->summary, 'url' => $url, 'touristType' => 'Visitors to Sri Lanka'];
        if ($package->destination) {
            $schema['itinerary'] = ['@type' => 'ItemList', 'name' => $package->destination];
        }
        if ($package->starting_price !== null) {
            $availability = match ($package->availability_status->value) {
                'available' => 'https://schema.org/InStock',
                'on_request' => 'https://schema.org/PreOrder',
                default => 'https://schema.org/SoldOut',
            };
            $schema['offers'] = ['@type' => 'Offer', 'price' => $package->starting_price, 'priceCurrency' => $package->currency, 'availability' => $availability, 'url' => $url];
        }
        if ($package->relationLoaded('images') && $package->images->isNotEmpty()) {
            $schema['image'] = $package->images->first()->url();
        }

        return $schema;
    }

    public function vehicleSchema(Vehicle $vehicle, string $url): array
    {
        $schema = ['@context' => 'https://schema.org', '@type' => 'Service', 'serviceType' => 'Vehicle rental', 'name' => $vehicle->title, 'description' => $vehicle->summary, 'url' => $url, 'provider' => ['@type' => 'Organization', 'name' => $this->organization()['name']]];
        if ($vehicle->rental_rate !== null) {
            $availability = match ($vehicle->availability_status->value) {
                'available' => 'https://schema.org/InStock',
                'on_request' => 'https://schema.org/PreOrder',
                default => 'https://schema.org/SoldOut',
            };
            $schema['offers'] = ['@type' => 'Offer', 'price' => $vehicle->rental_rate, 'priceCurrency' => $vehicle->currency, 'availability' => $availability, 'url' => $url];
        }
        if ($vehicle->relationLoaded('images') && $vehicle->images->isNotEmpty()) {
            $schema['image'] = $vehicle->images->first()->url();
        }

        return $schema;
    }

    public function propertySchema(Property $property, string $url): array
    {
        $schema = ['@context' => 'https://schema.org', '@type' => 'Accommodation', 'name' => $property->name, 'description' => $property->short_description, 'url' => $url];
        if ($property->location) {
            $schema['address'] = ['@type' => 'PostalAddress', 'addressLocality' => $property->location, 'addressCountry' => 'LK'];
        }
        if ($property->price !== null) {
            $schema['offers'] = ['@type' => 'Offer', 'price' => $property->price, 'priceCurrency' => $property->currency, 'url' => $url];
        }
        if ($property->max_guests) {
            $schema['occupancy'] = ['@type' => 'QuantitativeValue', 'maxValue' => $property->max_guests];
        }
        if ($property->relationLoaded('amenities')) {
            $schema['amenityFeature'] = $property->amenities->map(fn ($amenity): array => ['@type' => 'LocationFeatureSpecification', 'name' => $amenity->name, 'value' => true])->all();
        }
        if ($property->relationLoaded('images') && $property->images->isNotEmpty()) {
            $schema['image'] = $property->images->first()->url();
        }

        return $schema;
    }

    public function articleSchema(BlogPost $post, string $url): array
    {
        $schema = ['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $post->title, 'description' => $post->excerpt, 'datePublished' => $post->published_at?->toIso8601String(), 'dateModified' => $post->updated_at->toIso8601String(), 'author' => ['@type' => 'Person', 'name' => $post->author?->name ?: $this->organization()['name']], 'publisher' => ['@type' => 'Organization', 'name' => $this->organization()['name']], 'mainEntityOfPage' => $url];
        if ($post->featuredImageUrl()) {
            $schema['image'] = $post->featuredImageUrl();
        }

        return $schema;
    }
}
