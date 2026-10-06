<?php

namespace App\Notifications;

use App\Enums\InquiryType;
use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class NewInquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Inquiry $inquiry)
    {
        $this->onConnection('deferred')->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->inquiry->loadMissing([
            'packageDetails',
            'rentalDetails',
            'visaDetails',
            'baggageDetails',
            'propertyDetails',
        ]);

        return (new MailMessage)
            ->subject($this->subject())
            ->view('mail.inquiries.new', [
                'inquiry' => $this->inquiry,
                'heading' => $this->heading(),
                'details' => $this->details(),
                'adminUrl' => route('admin.inquiries.show', $this->inquiry),
            ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Deferred inquiry administrator notification failed.', [
            'inquiry_reference' => $this->inquiry->reference,
            'exception' => $exception::class,
        ]);
    }

    private function subject(): string
    {
        $subject = match ($this->inquiry->type) {
            InquiryType::Package => 'New Tour Package Inquiry - Ceylon Vacation Venues',
            InquiryType::Property => 'New Villa & House Inquiry - Ceylon Vacation Venues',
            InquiryType::Rental => 'New Vehicle Rental Inquiry - Ceylon Vacation Venues',
            InquiryType::Visa => 'New Visa Extension Request - Ceylon Vacation Venues',
            InquiryType::Baggage => 'New Lost Baggage Recovery Request - Ceylon Vacation Venues',
            InquiryType::General => 'New Contact Message - Ceylon Vacation Venues',
        };

        return "{$subject} [{$this->inquiry->reference}]";
    }

    private function heading(): string
    {
        return match ($this->inquiry->type) {
            InquiryType::Package => 'Tour Package Inquiry',
            InquiryType::Property => 'Villa & House Inquiry',
            InquiryType::Rental => 'Vehicle Rental Inquiry',
            InquiryType::Visa => 'Visa Extension Request',
            InquiryType::Baggage => 'Lost Baggage Recovery Request',
            InquiryType::General => 'Contact Message',
        };
    }

    /** @return array<string, string> */
    private function details(): array
    {
        $details = [
            'Reference' => $this->inquiry->reference,
            'Customer name' => $this->inquiry->name,
            'Email' => $this->inquiry->email,
        ];

        $this->add($details, 'Phone', $this->inquiry->phone);
        $this->add($details, 'Country', $this->inquiry->country_code);

        if ($detail = $this->inquiry->packageDetails) {
            $this->add($details, 'Tour package', $detail->package_title_snapshot);
            $this->add($details, 'Preferred start', $detail->preferred_start_date?->format('F j, Y'));
            $this->add($details, 'Preferred end', $detail->preferred_end_date?->format('F j, Y'));
            $details['Travellers'] = "{$detail->adults} adults, {$detail->children} children";
        } elseif ($detail = $this->inquiry->propertyDetails) {
            $this->add($details, 'Property', $detail->property_name_snapshot);
            $this->add($details, 'Check-in', $detail->check_in_date?->format('F j, Y'));
            $this->add($details, 'Check-out', $detail->check_out_date?->format('F j, Y'));
            $details['Guests'] = (string) $detail->guests;
        } elseif ($detail = $this->inquiry->rentalDetails) {
            $this->add($details, 'Vehicle', $detail->vehicle_title_snapshot);
            $this->add($details, 'Pickup date/time', $detail->pickup_at?->format('F j, Y g:i A'));
            $this->add($details, 'Pickup location', $detail->pickup_location);
            $this->add($details, 'Return date/time', $detail->return_at?->format('F j, Y g:i A'));
            $this->add($details, 'Return location', $detail->return_location);
            $details['Driver'] = $detail->driver_required ? 'Required' : 'Self drive';
        } elseif ($detail = $this->inquiry->visaDetails) {
            $this->add($details, 'Nationality', $detail->nationality_code);
            $this->add($details, 'Arrival date', $detail->arrival_date?->format('F j, Y'));
            $this->add($details, 'Current visa expiry', $detail->current_visa_expiry_date?->format('F j, Y'));
            $this->add($details, 'Requested extension', $detail->requested_extension_days ? "{$detail->requested_extension_days} days" : null);
        } elseif ($detail = $this->inquiry->baggageDetails) {
            $this->add($details, 'Arrival airport', $detail->pickup_location);
            $this->add($details, 'Airline and flight number', $detail->delivery_location);
            $details['Missing bags'] = (string) $detail->bag_count;
            $this->add($details, 'Baggage report/reference', $detail->special_instructions);
        }

        $this->add($details, 'Subject', $this->inquiry->subject);
        $this->add($details, 'Message', $this->inquiry->message);
        $details['Submitted'] = $this->inquiry->created_at->timezone(config('app.timezone'))->format('F j, Y g:i A T');

        return $details;
    }

    /** @param array<string, string> $details */
    private function add(array &$details, string $label, mixed $value): void
    {
        if ($value !== null && $value !== '') {
            $details[$label] = (string) $value;
        }
    }
}
