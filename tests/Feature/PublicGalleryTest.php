<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_gallery_displays_only_public_images_with_metadata_and_seo(): void
    {
        $album = GalleryAlbum::factory()->published()->create(['title' => 'Southern Coast', 'slug' => 'southern-coast']);
        GalleryImage::factory()->for($album, 'album')->create([
            'title' => 'Mirissa Sunset', 'caption' => 'Evening light over the Indian Ocean.', 'alt_text' => 'Orange sunset over Mirissa beach',
            'status' => PublicationStatus::Published, 'published_at' => now()->subMinute(), 'is_visible' => true,
            'variants' => ['small' => ['path' => 'gallery/small.webp'], 'medium' => ['path' => 'gallery/medium.webp']],
        ]);
        GalleryImage::factory()->for($album, 'album')->create(['title' => 'Draft photograph', 'status' => PublicationStatus::Draft, 'is_visible' => true]);
        GalleryImage::factory()->for($album, 'album')->create(['title' => 'Hidden photograph', 'status' => PublicationStatus::Published, 'published_at' => now()->subMinute(), 'is_visible' => false]);

        $this->get(route('gallery.index'))->assertOk()
            ->assertSee('<h1', false)->assertSeeText('Mirissa Sunset')->assertSeeText('Evening light over the Indian Ocean.')
            ->assertSee('alt="Orange sunset over Mirissa beach"', false)->assertSee('loading="lazy"', false)
            ->assertSee('srcset=', false)->assertSee('data-gallery-preview', false)->assertSee('rel="canonical"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)->assertDontSeeText('Draft photograph')->assertDontSeeText('Hidden photograph');
    }

    public function test_gallery_category_filter_and_pagination_preserve_query(): void
    {
        $coast = GalleryAlbum::factory()->published()->create(['title' => 'Coast', 'slug' => 'coast']);
        $hills = GalleryAlbum::factory()->published()->create(['title' => 'Hills', 'slug' => 'hills']);
        GalleryImage::factory()->for($hills, 'album')->create(['title' => 'Ella Hills', 'status' => PublicationStatus::Published, 'published_at' => now()->subMinute(), 'is_visible' => true]);
        GalleryImage::factory()->count(19)->for($coast, 'album')->create(['status' => PublicationStatus::Published, 'published_at' => now()->subMinute(), 'is_visible' => true]);

        $this->get(route('gallery.index', ['category' => 'coast']))->assertOk()->assertDontSeeText('Ella Hills')->assertSee('category=coast&amp;page=2', false);
    }

    public function test_gallery_has_database_backed_empty_state_without_coming_soon_copy(): void
    {
        $this->get(route('gallery.index'))->assertOk()->assertSeeText('No published photographs yet')->assertDontSeeText('Coming Soon')->assertDontSeeText('coming soon');
    }
}
