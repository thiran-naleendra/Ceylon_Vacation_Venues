<?php

namespace Tests\Feature;

use App\Enums\InquiryType;
use App\Models\Inquiry;
use App\Models\PackageInquiryDetail;
use App\Models\TourPackage;
use App\Models\User;
use App\Notifications\NewInquiryNotification;
use App\Services\InquirySubmissionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Tests\TestCase;

class InquiryAdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_inquiry_type_has_the_correct_branded_subject_and_admin_link(): void
    {
        config(['app.url' => 'https://ceylonvacationvenues.com']);
        URL::forceRootUrl('https://ceylonvacationvenues.com');
        URL::forceScheme('https');
        $administrator = User::factory()->administrator()->create();
        $subjects = [
            InquiryType::Package->value => 'New Tour Package Inquiry - Ceylon Vacation Venues',
            InquiryType::Property->value => 'New Villa & House Inquiry - Ceylon Vacation Venues',
            InquiryType::Rental->value => 'New Vehicle Rental Inquiry - Ceylon Vacation Venues',
            InquiryType::Visa->value => 'New Visa Extension Request - Ceylon Vacation Venues',
            InquiryType::Baggage->value => 'New Baggage Transport Request - Ceylon Vacation Venues',
            InquiryType::General->value => 'New Contact Message - Ceylon Vacation Venues',
        ];

        foreach ($subjects as $type => $subject) {
            $inquiry = Inquiry::factory()->create(['type' => $type]);
            $notification = new NewInquiryNotification($inquiry);
            $mail = $notification->toMail($administrator);

            $this->assertInstanceOf(ShouldQueue::class, $notification);
            $this->assertSame('deferred', $notification->connection);
            $this->assertTrue($notification->afterCommit);
            $this->assertSame("{$subject} [{$inquiry->reference}]", $mail->subject);
            $this->assertSame('mail.inquiries.new', $mail->view);
            $this->assertSame(route('admin.inquiries.show', $inquiry), $mail->viewData['adminUrl']);
            $this->assertStringStartsWith('https://ceylonvacationvenues.com/admin/inquiries/', $mail->viewData['adminUrl']);
        }
    }

    public function test_email_data_contains_relevant_submission_fields_and_escapes_customer_content(): void
    {
        $administrator = User::factory()->administrator()->create();
        $package = TourPackage::factory()->create(['title' => 'Southern <Escape>']);
        $inquiry = Inquiry::factory()->package()->create([
            'name' => '<script>alert(1)</script>',
            'email' => 'traveller@example.com',
            'phone' => '+94 77 123 4567',
            'subject' => 'Family holiday',
            'message' => '<b>Please call us</b>',
        ]);
        PackageInquiryDetail::factory()->for($inquiry)->for($package, 'tourPackage')->create([
            'package_title_snapshot' => 'Southern <Escape>',
            'adults' => 2,
            'children' => 1,
        ]);

        $mail = (new NewInquiryNotification($inquiry))->toMail($administrator);
        $details = $mail->viewData['details'];

        $this->assertSame('Southern <Escape>', $details['Tour package']);
        $this->assertSame('2 adults, 1 children', $details['Travellers']);
        $this->assertSame('Family holiday', $details['Subject']);
        $this->assertSame('<b>Please call us</b>', $details['Message']);

        $html = view($mail->view, $mail->viewData)->render();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<b>Please call us</b>', $html);
    }

    public function test_an_email_delivery_failure_does_not_lose_the_saved_inquiry(): void
    {
        User::factory()->owner()->create();
        Notification::shouldReceive('send')->once()->andThrow(new RuntimeException('Simulated queue outage'));

        $inquiry = app(InquirySubmissionService::class)->submit(InquiryType::General, [
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'phone' => '+94 77 123 4567',
            'subject' => 'Travel question',
            'message' => 'Please help with our itinerary.',
        ]);

        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiry->id,
            'type' => InquiryType::General->value,
            'email' => 'jane@example.com',
        ]);
    }
}
