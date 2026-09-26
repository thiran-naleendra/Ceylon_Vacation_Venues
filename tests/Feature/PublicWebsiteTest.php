<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Models\BlogPost;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\Page;
use App\Models\SocialLink;
use App\Models\TourPackage;
use App\Models\Vehicle;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_public_layout_uses_managed_brand_contact_social_and_navigation_data(): void
    {
        $this->setting('business', 'business.name', 'Managed Island Travel');
        $this->setting('contact', 'contact.phone', '+94 11 234 5678');
        $this->setting('contact', 'contact.email', 'hello@managed.test');
        $this->setting('contact', 'contact.whatsapp_number', '+94 77 555 1212');
        $this->setting('branding', 'branding.logo_path', 'branding/logo.webp');
        $this->setting('branding', 'branding.favicon_path', 'branding/favicon.webp');
        $this->setting('footer', 'footer.content', '<p>Managed footer information.</p>');
        SocialLink::factory()->create(['platform' => 'instagram', 'label' => 'Our Instagram', 'url' => 'https://instagram.com/managed']);
        Page::factory()->published()->create(['page_key' => 'about', 'title' => 'About our team', 'slug' => 'about-us']);

        $response = $this->get(route('blog.index'));

        $response->assertOk()
            ->assertSee('Managed Island Travel')
            ->assertSee('hello@managed.test')
            ->assertSee('+94 11 234 5678')
            ->assertSee('https://wa.me/94775551212', false)
            ->assertSee('/storage/branding/logo.webp', false)
            ->assertSee('/storage/branding/favicon.webp', false)
            ->assertSee('Managed footer information.')
            ->assertSee('Our Instagram')
            ->assertSee(route('about'), false)
            ->assertSee('aria-controls="mobile-navigation"', false);
    }

    public function test_home_uses_only_published_featured_and_visible_content(): void
    {
        Page::factory()->published()->create(['page_key' => 'home', 'title' => 'Home', 'slug' => 'home', 'summary' => 'Plan a thoughtful Sri Lanka holiday.']);
        $featuredPackage = TourPackage::factory()->published()->create(['title' => 'Published Featured Tour', 'destination' => 'Galle']);
        $featuredPackage->forceFill(['is_featured' => true])->save();
        $draftPackage = TourPackage::factory()->create(['title' => 'Draft Featured Tour']);
        $draftPackage->forceFill(['is_featured' => true])->save();
        $featuredVehicle = Vehicle::factory()->published()->create(['title' => 'Published Featured Car']);
        $featuredVehicle->forceFill(['is_featured' => true])->save();
        BlogPost::factory()->published()->create(['title' => 'Published Island Guide']);
        BlogPost::factory()->create(['title' => 'Draft Island Guide']);
        $album = GalleryAlbum::factory()->published()->create();
        GalleryImage::factory()->for($album, 'album')->create([
            'title' => 'Published Beach Photograph',
            'alt_text' => 'Published Beach Photograph',
            'status' => PublicationStatus::Published,
            'published_at' => now()->subMinute(),
            'is_visible' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSeeText('Published Featured Tour')
            ->assertDontSeeText('Draft Featured Tour')
            ->assertSeeText('Published Featured Car')
            ->assertSeeText('Published Island Guide')
            ->assertDontSeeText('Draft Island Guide')
            ->assertSee('alt="Published Beach Photograph"', false)
            ->assertDontSee('testimonial', false)
            ->assertDontSee('aggregateRating', false);
    }

    public function test_gallery_only_displays_published_visible_images_from_published_albums(): void
    {
        $publishedAlbum = GalleryAlbum::factory()->published()->create(['title' => 'Coast']);
        $draftAlbum = GalleryAlbum::factory()->create(['title' => 'Unpublished album']);
        GalleryImage::factory()->for($publishedAlbum, 'album')->create([
            'title' => 'Visible published image', 'status' => PublicationStatus::Published, 'published_at' => now()->subMinute(), 'is_visible' => true,
        ]);
        GalleryImage::factory()->for($publishedAlbum, 'album')->create([
            'title' => 'Draft image', 'status' => PublicationStatus::Draft, 'is_visible' => true,
        ]);
        GalleryImage::factory()->for($publishedAlbum, 'album')->create([
            'title' => 'Hidden image', 'status' => PublicationStatus::Published, 'published_at' => now()->subMinute(), 'is_visible' => false,
        ]);
        GalleryImage::factory()->for($draftAlbum, 'album')->create([
            'title' => 'Image in draft album', 'status' => PublicationStatus::Published, 'published_at' => now()->subMinute(), 'is_visible' => true,
        ]);

        $this->get(route('gallery.index'))
            ->assertOk()
            ->assertSeeText('Visible published image')
            ->assertDontSeeText('Draft image')
            ->assertDontSeeText('Hidden image')
            ->assertDontSeeText('Image in draft album')
            ->assertSee('<h1', false)
            ->assertSee('loading="lazy"', false);
    }

    private function setting(string $group, string $key, mixed $value): void
    {
        (new WebsiteSetting)->forceFill(compact('group', 'key', 'value'))->save();
    }
}
