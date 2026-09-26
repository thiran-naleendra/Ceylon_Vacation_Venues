<?php

namespace App\Enums;

enum InquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Completed = 'completed';
    case InProgress = 'in_progress';
    case AwaitingCustomer = 'awaiting_customer';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Completed => 'Completed',
            self::InProgress => 'In progress',
            self::AwaitingCustomer => 'Awaiting customer',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Spam => 'Spam',
        };
    }
}
