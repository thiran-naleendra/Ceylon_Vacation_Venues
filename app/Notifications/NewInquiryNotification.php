<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewInquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Inquiry $inquiry)
    {
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New {$this->inquiry->type->label()} inquiry {$this->inquiry->reference}")
            ->greeting('A new customer inquiry was received.')
            ->line("Customer: {$this->inquiry->name}")
            ->line("Email: {$this->inquiry->email}")
            ->action('View inquiry', route('admin.inquiries.show', $this->inquiry));
    }
}
