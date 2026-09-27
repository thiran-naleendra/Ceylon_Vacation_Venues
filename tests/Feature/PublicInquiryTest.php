<?php

namespace Tests\Feature;

use App\Enums\InquiryType;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WebsiteSetting;
use App\Notifications\NewInquiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicInquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Notification::fake();
    }

    public function test_guest_can_submit_package_and_vehicle_inquiries_with_server_owned_relations(): void
    {
        $owner = User::factory()->owner()->create();
        $administrator = User::factory()->administrator()->create();
        $package = TourPackage::factory()->published()->create(['title' => 'Island Explorer']);
        $packageToken = $this->formToken(route('inquiries.package.create', $package));
        $this->travel(3)->seconds();
        $this->post(route('inquiries.package.store', $package), [
            ...$this->contactData($packageToken), 'adults' => 2, 'children' => 1,
            'preferred_start_date' => now()->addMonth()->toDateString(),
        ])->assertRedirect()->assertSessionHas('inquiry_success');

        $this->assertDatabaseHas('inquiries', ['type' => InquiryType::Package->value, 'status' => 'new']);
        $this->assertDatabaseHas('package_inquiry_details', ['tour_package_id' => $package->id, 'package_title_snapshot' => 'Island Explorer']);

        $vehicle = Vehicle::factory()->published()->create(['title' => 'Coastal Car']);
        $vehicleToken = $this->formToken(route('inquiries.rental.create', $vehicle));
        $this->travel(3)->seconds();
        $this->post(route('inquiries.rental.store', $vehicle), [
            ...$this->contactData($vehicleToken),
            'pickup_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'return_at' => now()->addWeeks(2)->format('Y-m-d H:i:s'),
            'pickup_location' => 'Colombo Airport', 'return_location' => 'Galle', 'driver_required' => '1',
        ])->assertRedirect()->assertSessionHas('inquiry_success');

        $this->assertDatabaseHas('rental_inquiry_details', ['vehicle_id' => $vehicle->id, 'vehicle_category_id' => $vehicle->vehicle_category_id, 'vehicle_title_snapshot' => 'Coastal Car']);
        Notification::assertSentTo([$owner, $administrator], NewInquiryNotification::class);
        Notification::assertCount(4);
    }

    public function test_guest_can_submit_visa_baggage_and_contact_inquiries(): void
    {
        $owner = User::factory()->owner()->create();
        $administrator = User::factory()->administrator()->create();
        $visaToken = $this->formToken(route('inquiries.visa.create'));
        $this->travel(3)->seconds();
        $this->post(route('inquiries.visa.store'), [
            ...$this->contactData($visaToken), 'nationality_code' => 'GB',
            'arrival_date' => now()->subWeek()->toDateString(),
            'current_visa_expiry_date' => now()->addMonth()->toDateString(), 'requested_extension_days' => 30,
        ])->assertSessionHas('inquiry_success');

        $baggageToken = $this->formToken(route('inquiries.baggage.create'));
        $this->travel(3)->seconds();
        $this->post(route('inquiries.baggage.store'), [
            ...$this->contactData($baggageToken), 'pickup_location' => 'Kandy', 'delivery_location' => 'Ella',
            'pickup_at' => now()->addWeek()->format('Y-m-d H:i:s'), 'bag_count' => 3, 'estimated_weight_kg' => '42.50',
        ])->assertSessionHas('inquiry_success');

        $contactToken = $this->formToken(route('inquiries.contact.create'));
        $this->travel(3)->seconds();
        $this->post(route('inquiries.contact.store'), [
            ...$this->contactData($contactToken), 'subject' => 'Custom tour', 'message' => 'Please help plan our family holiday.',
        ])->assertSessionHas('inquiry_success');

        $this->assertDatabaseCount('inquiries', 3);
        $this->assertDatabaseCount('visa_inquiry_details', 1);
        $this->assertDatabaseCount('baggage_inquiry_details', 1);
        Notification::assertSentTo([$owner, $administrator], NewInquiryNotification::class);
        Notification::assertCount(6);
    }

    public function test_honeypot_fast_submissions_and_invalid_data_are_rejected(): void
    {
        $token = $this->formToken(route('inquiries.contact.create'));
        $this->post(route('inquiries.contact.store'), [
            ...$this->contactData($token), 'website' => 'spam.example', 'subject' => 'Spam', 'message' => 'This message should be rejected.',
        ])->assertSessionHasErrors(['website', 'form_token']);
        $this->assertDatabaseEmpty('inquiries');
    }

    public function test_notifications_only_target_active_owners_and_administrators_and_whatsapp_comes_from_settings(): void
    {
        $owner = User::factory()->owner()->create();
        $administrator = User::factory()->administrator()->create();
        $inactiveOwner = User::factory()->owner()->inactive()->create();
        $editor = User::factory()->editor()->create();
        $inquiryAgent = User::factory()->inquiryAgent()->create();
        (new WebsiteSetting)->forceFill(['group' => 'contact', 'key' => 'contact.whatsapp_number', 'value' => '+94 77 123 4567'])->save();
        $response = $this->get(route('inquiries.contact.create'))->assertOk()->assertSee('https://wa.me/94771234567', false);
        $token = array_key_last($this->app['session']->get('inquiry_form_tokens'));
        $this->travel(3)->seconds();
        $this->post(route('inquiries.contact.store'), [
            ...$this->contactData($token), 'subject' => 'Airport transfer', 'message' => 'Please send information about your transfer service.',
        ])->assertSessionHas('inquiry_success');

        Notification::assertSentTo([$owner, $administrator], NewInquiryNotification::class);
        Notification::assertNotSentTo([$inactiveOwner, $editor, $inquiryAgent], NewInquiryNotification::class);
        Notification::assertCount(2);
    }

    private function formToken(string $url): string
    {
        $response = $this->get($url)->assertOk();

        return (string) array_key_last($this->app['session']->get('inquiry_form_tokens'));
    }

    /** @return array<string, mixed> */
    private function contactData(string $token): array
    {
        return ['name' => 'Jane Traveller', 'email' => 'jane@example.com', 'phone' => '+44 7000 123456', 'country_code' => 'GB', 'privacy_accepted' => '1', 'form_token' => $token, 'website' => ''];
    }
}
