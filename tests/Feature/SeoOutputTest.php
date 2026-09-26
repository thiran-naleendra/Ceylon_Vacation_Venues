<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\TourPackage;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoOutputTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_page_outputs_complete_metadata_and_valid_structured_data(): void
    {
        $page = Page::factory()->published()->create([
            'page_key' => 'about',
            'title' => 'About our Sri Lanka team',
            'slug' => 'about-us',
            'summary' => 'Local travel specialists in Sri Lanka.',
        ]);
        $page->seoMetadata()->create([
            'meta_title' => 'About Ceylon Vacation Venues',
            'meta_description' => 'Meet our local Sri Lankan travel specialists.',
            'canonical_url' => 'https://ceylonvacationvenues.com/about-us',
            'robots_index' => true,
            'robots_follow' => true,
            'og_title' => 'Meet our travel team',
            'og_description' => 'Local Sri Lankan travel expertise.',
        ]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('<title>About Ceylon Vacation Venues</title>', false)
            ->assertSee('name="description" content="Meet our local Sri Lankan travel specialists."', false)
            ->assertSee('rel="canonical" href="https://ceylonvacationvenues.com/about-us"', false)
            ->assertSee('name="robots" content="index,follow"', false)
            ->assertSee('property="og:title" content="Meet our travel team"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('<h1', false);
    }

    public function test_package_and_vehicle_pages_output_suitable_schema(): void
    {
        $package = TourPackage::factory()->published()->create(['title' => 'Coastal Sri Lanka']);
        $vehicle = Vehicle::factory()->published()->create(['title' => 'Island Touring Car']);

        $this->get(route('packages.show', $package))
            ->assertOk()
            ->assertSee('"@type":"TouristTrip"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);

        $this->get(route('vehicles.show', $vehicle))
            ->assertOk()
            ->assertSee('"@type":"Service"', false)
            ->assertSee('"serviceType":"Vehicle rental"', false);
    }

    public function test_home_page_outputs_organization_and_website_schema_without_ratings(): void
    {
        Page::factory()->published()->create([
            'page_key' => 'home',
            'title' => 'Ceylon Vacation Venues',
            'slug' => 'home',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertDontSee('aggregateRating', false)
            ->assertDontSee('reviewRating', false);
    }

    public function test_sitemap_contains_only_published_indexable_content(): void
    {
        $page = Page::factory()->published()->create(['page_key' => 'about', 'slug' => 'about-us']);
        $package = TourPackage::factory()->published()->create();
        $vehicle = Vehicle::factory()->published()->create();
        $post = BlogPost::factory()->published()->create();
        $draft = TourPackage::factory()->create();
        $noindex = Page::factory()->published()->create(['page_key' => 'privacy-policy', 'slug' => 'privacy-policy']);
        $noindex->seoMetadata()->create(['robots_index' => false, 'robots_follow' => true]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('about'), false)
            ->assertSee(route('packages.show', $package), false)
            ->assertSee(route('vehicles.show', $vehicle), false)
            ->assertSee(route('blog.show', $post), false)
            ->assertDontSee(route('packages.show', $draft), false)
            ->assertDontSee(route('privacy'), false)
            ->assertDontSee('/admin', false)
            ->assertDontSee('/login', false);
    }

    public function test_robots_file_blocks_admin_and_links_to_sitemap(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.route('sitemap'));
    }
}
