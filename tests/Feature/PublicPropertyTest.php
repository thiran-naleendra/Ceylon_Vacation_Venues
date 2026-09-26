<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyType;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_listing_filters_published_properties_by_type_location_and_guests(): void
    {
        $villa = PropertyType::where('slug', 'villa')->firstOrFail();
        $house = PropertyType::where('slug', 'house')->firstOrFail();
        Property::factory()->for($villa, 'type')->published()->create(['name' => 'Mirissa Family Villa', 'location' => 'Mirissa', 'max_guests' => 8]);
        Property::factory()->for($house, 'type')->published()->create(['name' => 'Galle House', 'location' => 'Galle', 'max_guests' => 4]);
        Property::factory()->for($villa, 'type')->create(['name' => 'Draft Mirissa Villa', 'location' => 'Mirissa', 'max_guests' => 10]);
        $this->get(route('properties.index', ['type' => 'villa', 'location' => 'Mirissa', 'guests' => 6]))->assertOk()->assertSeeText('Mirissa Family Villa')->assertDontSeeText('Galle House')->assertDontSeeText('Draft Mirissa Villa')->assertSee('name="type"', false)->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_detail_has_gallery_amenities_inquiry_whatsapp_and_seo_schema(): void
    {
        $property = Property::factory()->published()->create(['name' => 'Palm Cove Villa', 'location' => 'Weligama', 'max_guests' => 6]);
        $amenity = Amenity::factory()->create(['name' => 'Swimming Pool']);
        $property->amenities()->attach($amenity);
        PropertyImage::factory()->for($property)->create(['alt_text' => 'Palm Cove pool', 'variants' => ['small' => ['path' => 'properties/small.webp'], 'medium' => ['path' => 'properties/medium.webp']]]);
        $related = Property::factory()->for($property->type, 'type')->published()->create(['name' => 'Related Beach Villa']);
        $seo = $property->seoMetadata()->create(['meta_title' => 'Palm Cove Villa | Sri Lanka', 'meta_description' => 'Private villa in Weligama.', 'robots_index' => true, 'robots_follow' => true]);
        (new WebsiteSetting)->forceFill(['group' => 'contact', 'key' => 'contact.whatsapp_number', 'value' => '94770001122'])->save();
        $this->get(route('properties.show', $property))->assertOk()->assertSee('<title>Palm Cove Villa | Sri Lanka</title>', false)->assertSeeText('Swimming Pool')->assertSeeText($related->name)->assertSee(route('inquiries.property.create', $property), false)->assertSee('https://wa.me/94770001122?text=', false)->assertSee('alt="Palm Cove pool"', false)->assertSee('"@type":"Accommodation"', false);
    }

    public function test_draft_scheduled_invalid_and_unpublished_type_properties_return_not_found(): void
    {
        $draft = Property::factory()->create();
        $scheduled = Property::factory()->create(['status' => 'published', 'published_at' => now()->addDay()]);
        $hiddenType = PropertyType::factory()->create();
        $hidden = Property::factory()->for($hiddenType, 'type')->published()->create();
        $this->get(route('properties.show', $draft))->assertNotFound();
        $this->get(route('properties.show', $scheduled))->assertNotFound();
        $this->get(route('properties.show', $hidden))->assertNotFound();
        $this->get('/villas-houses/no-such-property')->assertNotFound();
    }

    public function test_sitemap_contains_only_published_indexable_properties(): void
    {
        $visible = Property::factory()->published()->create();
        $draft = Property::factory()->create();
        $hidden = Property::factory()->published()->create();
        $hidden->seoMetadata()->create(['robots_index' => false, 'robots_follow' => true]);
        $this->get(route('sitemap'))->assertOk()->assertSee(route('properties.show', $visible), false)->assertDontSee(route('properties.show', $draft), false)->assertDontSee(route('properties.show', $hidden), false);
    }
}
