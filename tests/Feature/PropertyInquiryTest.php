<?php

namespace Tests\Feature;

use App\Enums\InquiryType;
use App\Models\Property;
use App\Models\User;
use App\Notifications\NewInquiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PropertyInquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Notification::fake();
    }

    public function test_guest_can_submit_property_inquiry_with_server_owned_relation(): void
    {
        $owner = User::factory()->owner()->create();
        $administrator = User::factory()->administrator()->create();
        $property = Property::factory()->published()->create(['name' => 'Secure Villa', 'max_guests' => 4]);
        $this->get(route('inquiries.property.create', $property))->assertOk();
        $token = (string) array_key_last($this->app['session']->get('inquiry_form_tokens'));
        $this->travel(3)->seconds();
        $this->post(route('inquiries.property.store', $property), ['name' => 'Jane Traveller', 'email' => 'jane@example.com', 'phone' => '+44 7000 123456', 'country_code' => 'GB', 'privacy_accepted' => '1', 'form_token' => $token, 'website' => '', 'check_in_date' => now()->addWeek()->toDateString(), 'check_out_date' => now()->addWeeks(2)->toDateString(), 'guests' => 3, 'message' => 'Please confirm availability.', 'property_id' => 999999])->assertRedirect()->assertSessionHas('inquiry_success');
        $this->assertDatabaseHas('inquiries', ['type' => InquiryType::Property->value, 'name' => 'Jane Traveller']);
        $this->assertDatabaseHas('property_inquiry_details', ['property_id' => $property->id, 'property_name_snapshot' => 'Secure Villa', 'guests' => 3]);
        $this->assertDatabaseMissing('property_inquiry_details', ['property_id' => 999999]);
        Notification::assertSentTo([$owner, $administrator], NewInquiryNotification::class);
        Notification::assertCount(2);
    }

    public function test_inquiry_validation_spam_protection_and_draft_access_are_enforced(): void
    {
        $property = Property::factory()->published()->create();
        $this->get(route('inquiries.property.create', $property));
        $token = (string) array_key_last($this->app['session']->get('inquiry_form_tokens'));
        $this->post(route('inquiries.property.store', $property), ['name' => 'Spam User', 'email' => 'spam@example.com', 'privacy_accepted' => '1', 'form_token' => $token, 'website' => 'spam.test', 'check_in_date' => now()->subDay()->toDateString(), 'check_out_date' => now()->subDays(2)->toDateString(), 'guests' => 0])->assertSessionHasErrors(['website', 'form_token', 'check_in_date', 'check_out_date', 'guests']);
        $this->assertDatabaseEmpty('inquiries');
        $draft = Property::factory()->create();
        $this->get(route('inquiries.property.create', $draft))->assertNotFound();
    }
}
