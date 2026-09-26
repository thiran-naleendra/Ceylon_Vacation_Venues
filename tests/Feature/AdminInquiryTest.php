<?php

namespace Tests\Feature;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_inquiry_agent_can_search_filter_and_view_inquiries(): void
    {
        $agent = User::factory()->inquiryAgent()->create();
        Inquiry::factory()->visa()->create(['name' => 'Matching Visitor', 'status' => InquiryStatus::New]);
        Inquiry::factory()->baggage()->create(['name' => 'Hidden Visitor']);

        $this->actingAs($agent)->get(route('admin.inquiries.index', ['search' => 'Matching', 'type' => 'visa', 'status' => 'new']))
            ->assertOk()->assertSeeText('Matching Visitor')->assertDontSeeText('Hidden Visitor');
    }

    public function test_inquiry_agent_can_update_status_and_add_internal_notes(): void
    {
        $agent = User::factory()->inquiryAgent()->create();
        $inquiry = Inquiry::factory()->create();

        $this->actingAs($agent)->patch(route('admin.inquiries.status', $inquiry), ['status' => 'contacted', 'reason' => 'Called customer'])->assertRedirect();
        $this->actingAs($agent)->post(route('admin.inquiries.notes.store', $inquiry), ['body' => 'Customer prefers WhatsApp.'])->assertRedirect();

        $inquiry->refresh();
        $this->assertSame(InquiryStatus::Contacted, $inquiry->status);
        $this->assertDatabaseHas('inquiry_status_histories', ['inquiry_id' => $inquiry->id, 'changed_by' => $agent->id, 'to_status' => 'contacted']);
        $this->assertDatabaseHas('inquiry_notes', ['inquiry_id' => $inquiry->id, 'author_id' => $agent->id, 'body' => 'Customer prefers WhatsApp.']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'inquiry.note_added', 'subject_id' => $inquiry->id]);
    }

    public function test_completed_status_sets_closed_time_and_reopening_clears_it(): void
    {
        $agent = User::factory()->inquiryAgent()->create();
        $inquiry = Inquiry::factory()->create();
        $this->actingAs($agent)->patch(route('admin.inquiries.status', $inquiry), ['status' => 'completed']);
        $this->assertNotNull($inquiry->refresh()->closed_at);
        $this->actingAs($agent)->patch(route('admin.inquiries.status', $inquiry), ['status' => 'new']);
        $this->assertNull($inquiry->refresh()->closed_at);
    }

    public function test_editor_and_guest_cannot_access_inquiry_administration(): void
    {
        $inquiry = Inquiry::factory()->create();
        $this->get(route('admin.inquiries.index'))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->editor()->create())->get(route('admin.inquiries.index'))->assertForbidden();
        $this->actingAs(User::factory()->editor()->create())->get(route('admin.inquiries.show', $inquiry))->assertForbidden();
    }
}
