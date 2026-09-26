<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\Inquiry;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Services\AllowedHtmlSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_editor_dashboard_does_not_disclose_customer_inquiries(): void
    {
        $editor = User::factory()->editor()->create();
        Inquiry::factory()->create(['name' => 'Private Customer', 'email' => 'private@example.com']);

        $this->actingAs($editor)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private')
            ->assertDontSeeText('Recent inquiries')
            ->assertDontSeeText('Private Customer')
            ->assertDontSee('private@example.com');
    }

    public function test_security_headers_are_applied_to_public_and_admin_responses(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private');
    }

    public function test_rich_html_rejects_protocol_relative_encoded_and_executable_links(): void
    {
        $sanitized = app(AllowedHtmlSanitizer::class)->sanitize(
            '<a href="//evil.example">one</a><a href="/safe-page">safe</a><a href="/%2f%2fevil.example">encoded</a><a href="javascript:alert(1)">script</a>',
        );

        $this->assertStringNotContainsString('href="//evil.example"', $sanitized);
        $this->assertStringContainsString('href="/safe-page"', $sanitized);
        $this->assertStringNotContainsString('href="/%2f%2fevil.example"', $sanitized);
        $this->assertStringNotContainsString('javascript:', $sanitized);
    }

    public function test_vehicle_inquiry_cannot_expose_a_vehicle_in_an_unpublished_category(): void
    {
        $category = VehicleCategory::factory()->create();
        $vehicle = Vehicle::factory()->published()->for($category, 'category')->create();

        $this->get(route('inquiries.rental.create', $vehicle))->assertNotFound();
    }

    public function test_disguised_executable_upload_is_rejected_before_storage(): void
    {
        $owner = User::factory()->owner()->create();
        $album = GalleryAlbum::factory()->create();
        $upload = UploadedFile::fake()->createWithContent('holiday.jpg', '<?php echo "unsafe";');

        $this->actingAs($owner)->post(route('admin.gallery.store'), [
            'gallery_album_id' => $album->id,
            'title' => 'Unsafe upload',
            'alt_text' => 'Unsafe upload',
            'sort_order' => 0,
            'status' => 'draft',
            'image' => $upload,
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseCount((new GalleryImage)->getTable(), 0);
    }

    public function test_admin_content_mutations_create_append_only_audits_without_request_values(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post(route('admin.redirects.store'), [
            'source_path' => '/private-old-path',
            'destination_path' => '/private-new-path',
            'status_code' => 301,
            'is_active' => true,
        ])->assertRedirect();

        $audit = AuditLog::query()->where('action', 'admin.redirect.created')->sole();
        $this->assertSame($owner->id, $audit->actor_id);
        $this->assertContains('source_path', $audit->metadata['changed_fields']);
        $this->assertStringNotContainsString('/private-new-path', json_encode($audit->metadata, JSON_THROW_ON_ERROR));
    }
}
