<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\TourPackage;
use App\Models\WebsiteSetting;
use App\Services\PublicSiteData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_public_site_data_is_resolved_only_once_per_request_lifecycle(): void
    {
        WebsiteSetting::factory()->create();
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $siteData = app(PublicSiteData::class);
        $siteData->get();
        $siteData->get();

        $settingQueries = array_filter($queries, fn (string $query): bool => str_contains($query, 'from "website_settings"'));
        $this->assertCount(1, $settingQueries);
    }

    public function test_related_blog_cards_are_eager_loaded_and_use_responsive_images(): void
    {
        $post = BlogPost::factory()->published()->create([
            'featured_image_path' => 'blog/large.webp',
            'featured_image_variants' => [
                'small' => ['path' => 'blog/small.webp', 'width' => 480, 'height' => 270],
                'medium' => ['path' => 'blog/medium.webp', 'width' => 960, 'height' => 540],
            ],
        ]);
        BlogPost::factory()->count(3)->published()->create(['blog_category_id' => $post->blog_category_id]);
        $categoryQueries = 0;
        DB::listen(function ($query) use (&$categoryQueries): void {
            if (str_contains($query->sql, 'blog_categories')) {
                $categoryQueries++;
            }
        });

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('blog/small.webp', false)
            ->assertSee('blog/medium.webp', false)
            ->assertSee('srcset=', false);

        $this->assertLessThanOrEqual(2, $categoryQueries);
    }

    public function test_sitemap_cache_is_invalidated_when_public_content_changes(): void
    {
        Page::factory()->published()->create(['page_key' => 'home', 'slug' => 'home']);
        $this->get(route('sitemap'))->assertOk();

        $package = TourPackage::factory()->published()->create();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('packages.show', $package), false);
    }
}
