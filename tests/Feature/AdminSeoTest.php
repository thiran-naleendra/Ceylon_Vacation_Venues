<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_editor_can_browse_and_filter_supported_content_types(): void
    {
        $package = TourPackage::factory()->create(['title' => 'Coastal Discovery']);

        $this->actingAs(User::factory()->editor()->create())
            ->get(route('admin.seo.index', ['type' => 'packages', 'search' => 'Coastal']))
            ->assertOk()
            ->assertSeeText('SEO management')
            ->assertSeeText('Coastal Discovery')
            ->assertSee(route('admin.seo.edit', ['packages', $package->id]), false);
    }

    public function test_editor_can_create_and_update_metadata_from_the_seo_module(): void
    {
        $page = Page::factory()->published()->create(['page_key' => 'home', 'slug' => 'home', 'title' => 'Home']);
        $data = [
            'meta_title' => 'Sri Lanka Holidays | Ceylon Vacation Venues',
            'meta_description' => 'Plan tours, stays and transport throughout Sri Lanka.',
            'canonical_url' => 'https://ceylonvacationvenues.com/',
            'robots_index' => '1',
            'robots_follow' => '1',
            'og_title' => 'Discover Sri Lanka',
            'og_description' => 'Plan your Sri Lanka journey.',
            'og_image_alt' => 'Sri Lankan tropical coastline',
        ];

        $this->actingAs(User::factory()->editor()->create())
            ->put(route('admin.seo.update', ['pages', $page->id]), $data)
            ->assertRedirect(route('admin.seo.edit', ['pages', $page->id]));

        $this->assertDatabaseHas('seo_metadata', [
            'page_id' => $page->id,
            'meta_title' => $data['meta_title'],
            'robots_index' => true,
        ]);
    }

    public function test_seo_updates_validate_urls_and_block_unauthorized_roles(): void
    {
        $page = Page::factory()->published()->create(['page_key' => 'home', 'slug' => 'home', 'title' => 'Home']);

        $this->actingAs(User::factory()->editor()->create())
            ->put(route('admin.seo.update', ['pages', $page->id]), [
                'canonical_url' => 'http://insecure.test',
                'robots_index' => '1',
                'robots_follow' => '1',
            ])->assertSessionHasErrors('canonical_url');

        $this->actingAs(User::factory()->inquiryAgent()->create())
            ->get(route('admin.seo.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->editor()->create())
            ->get(route('admin.seo.edit', ['unknown', $page->id]))
            ->assertNotFound();
    }
}
