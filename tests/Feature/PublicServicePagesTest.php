<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SocialLink;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicServicePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_visa_page_combines_published_cms_content_secure_form_whatsapp_and_schema(): void
    {
        $page = Page::factory()->published()->create([
            'page_key' => 'visa-extension', 'slug' => 'visa-extension-help', 'title' => 'Visa Extension Assistance',
            'summary' => 'Visa support summary.', 'body' => '<h2>How we can assist</h2><p>Published visa service content.</p>',
        ]);
        $this->setting('contact.whatsapp_number', '+94 77 123 4567');

        $response = $this->get(route('services.visa'));

        $response->assertOk()
            ->assertSeeText('Published visa service content.')
            ->assertSee('action="'.route('inquiries.visa.store').'"', false)
            ->assertSee('name="form_token"', false)
            ->assertSee('name="website"', false)
            ->assertSee('https://wa.me/94771234567', false)
            ->assertSee('"@type":"Service"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('rel="canonical" href="'.route('services.visa').'"', false);

        $this->get(route('pages.show', $page))->assertRedirect(route('services.visa'))->assertStatus(301);
    }

    public function test_baggage_page_uses_published_content_and_hides_draft_content(): void
    {
        Page::factory()->create([
            'page_key' => 'baggage-transport', 'slug' => 'private-baggage-draft', 'title' => 'Draft baggage title',
            'body' => '<p>Private draft copy.</p>',
        ]);

        $this->get(route('services.baggage'))
            ->assertOk()
            ->assertSeeText('Airport Baggage Recovery')
            ->assertSee('action="'.route('inquiries.baggage.store').'"', false)
            ->assertDontSeeText('Private draft copy.');
    }

    public function test_fixed_information_pages_use_required_paths_and_legacy_slugs_redirect(): void
    {
        $about = Page::factory()->published()->create(['page_key' => 'about', 'slug' => 'about-us', 'title' => 'About our team', 'body' => '<p>Our published story.</p>']);
        Page::factory()->published()->create(['page_key' => 'privacy-policy', 'slug' => 'our-privacy', 'title' => 'Privacy Policy', 'body' => '<p>Privacy content.</p>']);
        Page::factory()->published()->create(['page_key' => 'terms-and-conditions', 'slug' => 'our-terms', 'title' => 'Terms & Conditions', 'body' => '<p>Terms content.</p>']);

        $this->get(route('about'))->assertOk()->assertSeeText('Our published story.')->assertSee('rel="canonical" href="'.route('about').'"', false);
        $this->get(route('privacy'))->assertOk()->assertSeeText('Privacy content.');
        $this->get(route('terms'))->assertOk()->assertSeeText('Terms content.');
        $this->get(route('pages.show', $about))->assertStatus(301)->assertRedirect(route('about'));
    }

    public function test_contact_page_uses_business_details_active_social_links_optional_map_and_secure_form(): void
    {
        Page::factory()->published()->create(['page_key' => 'contact', 'slug' => 'contact-us', 'title' => 'Contact our team', 'body' => '<p>Tell us about your journey.</p>']);
        $this->setting('business.name', 'CVV Managed Travel');
        $this->setting('contact.phone', '+94 11 222 3333');
        $this->setting('contact.email', 'travel@example.com');
        $this->setting('contact.address', "Colombo\nSri Lanka");
        $this->setting('contact.whatsapp_number', '+94 77 123 4567');
        $this->setting('contact.map_embed_url', 'https://www.google.com/maps/embed?pb=configured');
        SocialLink::factory()->create(['label' => 'Managed Instagram', 'is_active' => true]);
        SocialLink::factory()->create(['platform' => 'facebook', 'label' => 'Hidden Facebook', 'url' => 'https://facebook.com/hidden', 'is_active' => false]);

        $response = $this->get(route('inquiries.contact.create'));

        $response->assertOk()
            ->assertSeeText('Tell us about your journey.')
            ->assertSeeText('+94 11 222 3333')
            ->assertSeeText('travel@example.com')
            ->assertSeeText('Managed Instagram')
            ->assertDontSeeText('Hidden Facebook')
            ->assertSee('https://www.google.com/maps/embed?pb=configured', false)
            ->assertSee('loading="lazy"', false)
            ->assertSee('action="'.route('inquiries.contact.store').'"', false)
            ->assertSee('name="form_token"', false);
    }

    public function test_contact_map_is_not_rendered_when_not_configured(): void
    {
        $this->get(route('inquiries.contact.create'))
            ->assertOk()
            ->assertDontSee('<iframe', false)
            ->assertDontSeeText('Find us');
    }

    private function setting(string $key, mixed $value): void
    {
        (new WebsiteSetting)->forceFill(['group' => str($key)->before('.')->toString(), 'key' => $key, 'value' => $value])->save();
    }
}
