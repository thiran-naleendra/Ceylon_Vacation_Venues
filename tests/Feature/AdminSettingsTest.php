<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_administrator_can_update_business_branding_contact_footer_and_social_settings(): void
    {
        $response = $this->actingAs(User::factory()->administrator()->create())->put(route('admin.settings.update'), [
            ...$this->validSettings(),
            'logo' => UploadedFile::fake()->image('logo.png', 800, 400),
            'favicon' => UploadedFile::fake()->image('favicon.png', 256, 256),
            'footer_content' => '<p>Travel with us.</p><script>alert(1)</script><a href="javascript:bad()">bad</a>',
            'social_links' => [['platform' => 'instagram', 'label' => 'Instagram', 'url' => 'https://instagram.com/ceylonvacationvenues', 'is_active' => '1', 'sort_order' => 0]],
        ]);

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertSame('Ceylon Vacation Venues', WebsiteSetting::query()->where('key', 'business.name')->sole()->value);
        $this->assertSame('+94771234567', WebsiteSetting::query()->where('key', 'contact.whatsapp_number')->sole()->value);
        $this->assertSame('https://www.google.com/maps/embed?pb=managed', WebsiteSetting::query()->where('key', 'contact.map_embed_url')->sole()->value);
        $footer = WebsiteSetting::query()->where('key', 'footer.content')->sole()->value;
        $this->assertStringNotContainsString('<script', $footer);
        $this->assertStringNotContainsString('javascript:', $footer);
        $logo = WebsiteSetting::query()->where('key', 'branding.logo_path')->sole()->value;
        $favicon = WebsiteSetting::query()->where('key', 'branding.favicon_path')->sole()->value;
        Storage::disk('public')->assertExists($logo);
        Storage::disk('public')->assertExists($favicon);
        $this->assertDatabaseHas('social_links', ['platform' => 'instagram', 'is_active' => true]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'settings.updated']);
    }

    public function test_only_owner_and_administrator_can_manage_settings(): void
    {
        $this->actingAs(User::factory()->editor()->create())->get(route('admin.settings.index'))->assertForbidden();
        $this->actingAs(User::factory()->inquiryAgent()->create())->put(route('admin.settings.update'), $this->validSettings())->assertForbidden();
    }

    public function test_social_urls_and_branding_files_are_strictly_validated(): void
    {
        $data = $this->validSettings();
        $data['logo'] = UploadedFile::fake()->create('logo.svg', 5, 'image/svg+xml');
        $data['social_links'] = [['platform' => 'unknown', 'label' => '', 'url' => 'http://unsafe.example.com', 'is_active' => '1', 'sort_order' => 0]];

        $this->actingAs(User::factory()->administrator()->create())->put(route('admin.settings.update'), $data)
            ->assertSessionHasErrors(['logo', 'social_links.0.platform', 'social_links.0.url']);
        $this->assertDatabaseEmpty('website_settings');
        $this->assertDatabaseEmpty('social_links');
    }

    /** @return array<string, mixed> */
    private function validSettings(): array
    {
        return [
            'business_name' => 'Ceylon Vacation Venues', 'legal_name' => 'Ceylon Vacation Venues Private Limited',
            'registration_number' => 'PV-123', 'phone' => '+94112345678', 'whatsapp_number' => '+94771234567',
            'email' => 'hello@example.com', 'notification_email' => 'inquiries@example.com',
            'address' => 'Colombo, Sri Lanka', 'map_embed_url' => 'https://www.google.com/maps/embed?pb=managed', 'footer_content' => '<p>Travel in Sri Lanka.</p>',
        ];
    }
}
