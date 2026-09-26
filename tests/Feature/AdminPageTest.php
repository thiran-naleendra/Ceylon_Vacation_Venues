<?php

namespace Tests\Feature;

use App\Enums\PublicationStatus;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\User;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_seeder_creates_the_seven_fixed_pages_without_duplicates(): void
    {
        $this->seed(PageSeeder::class);
        $this->seed(PageSeeder::class);

        $this->assertDatabaseCount('pages', 7);
        $this->assertSame(['about', 'baggage-transport', 'contact', 'home', 'privacy-policy', 'terms-and-conditions', 'visa-extension'], Page::query()->orderBy('page_key')->pluck('page_key')->all());
    }

    public function test_editor_can_update_page_hero_seo_and_sanitized_content(): void
    {
        $page = Page::factory()->create(['page_key' => 'about', 'title' => 'About', 'slug' => 'about-us']);
        $response = $this->actingAs(User::factory()->editor()->create())->put(route('admin.pages.update', $page), [
            'title' => 'About Ceylon Vacation Venues', 'slug' => 'about-us', 'summary' => 'Local travel specialists.',
            'body' => '<h2>Our story</h2><script>alert(1)</script><p onclick="bad()">Welcome <a href="javascript:alert(1)">here</a> <a href="https://example.com" target="_blank">safe</a></p>',
            'hero' => ['title' => 'Discover Sri Lanka with us', 'text' => 'Local knowledge and thoughtful service.', 'image' => UploadedFile::fake()->image('hero.jpg', 1600, 900), 'image_alt' => 'Sri Lankan coast at sunset'],
            'seo' => ['meta_title' => 'About our Sri Lanka travel team', 'meta_description' => 'Meet our travel team.', 'canonical_url' => 'https://ceylonvacationvenues.com/about-us', 'robots_index' => '1', 'robots_follow' => '1', 'og_title' => 'About us', 'og_description' => 'Local specialists.', 'og_image_alt' => 'Sri Lankan coast'],
            'seo_use_hero_image' => '1',
        ]);

        $response->assertRedirect(route('admin.pages.edit', $page));
        $page->refresh();
        $this->assertStringNotContainsString('<script', $page->body);
        $this->assertStringNotContainsString('onclick', $page->body);
        $this->assertStringNotContainsString('javascript:', $page->body);
        $this->assertStringContainsString('rel="noopener noreferrer"', $page->body);
        $hero = $page->sections()->where('section_key', 'hero')->sole()->content;
        $this->assertSame('Sri Lankan coast at sunset', $hero['image']['alt']);
        Storage::disk('public')->assertExists($hero['image']['path']);
        Storage::disk('public')->assertExists($hero['image']['variants']['small']['path']);
        $this->assertSame($hero['image']['path'], $page->seoMetadata->og_image_path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'page.updated', 'subject_id' => $page->id]);
    }

    public function test_editor_can_publish_page_and_slug_change_creates_a_permanent_redirect(): void
    {
        $editor = User::factory()->editor()->create();
        $page = Page::factory()->create();
        $this->actingAs($editor)->patch(route('admin.pages.status', $page), ['status' => 'published'])->assertRedirect();
        $this->assertSame(PublicationStatus::Published, $page->refresh()->status);

        $oldPath = '/'.$page->slug;
        $this->actingAs($editor)->put(route('admin.pages.update', $page), $this->validPageData($page, ['slug' => 'changed-slug']))->assertRedirect();

        $this->assertSame('changed-slug', $page->refresh()->slug);
        $redirect = Redirect::query()->where('source_path', $oldPath)->sole();
        $this->assertSame('/changed-slug', $redirect->destination_path);
        $this->assertSame(301, $redirect->status_code);
    }

    public function test_inquiry_agent_cannot_manage_pages(): void
    {
        $page = Page::factory()->create();
        $agent = User::factory()->inquiryAgent()->create();
        $this->actingAs($agent)->get(route('admin.pages.index'))->assertForbidden();
        $this->actingAs($agent)->get(route('admin.pages.edit', $page))->assertForbidden();
    }

    /** @param array<string, mixed> $changes */
    private function validPageData(Page $page, array $changes = []): array
    {
        return array_replace_recursive([
            'title' => $page->title, 'slug' => $page->slug, 'summary' => '', 'body' => '<p>Page content</p>',
            'hero' => ['title' => '', 'text' => '', 'delete_image' => '0'],
            'seo' => ['meta_title' => '', 'meta_description' => '', 'canonical_url' => '', 'robots_index' => '1', 'robots_follow' => '1', 'og_title' => '', 'og_description' => '', 'og_image_alt' => ''],
            'seo_use_hero_image' => '0',
        ], $changes);
    }
}
