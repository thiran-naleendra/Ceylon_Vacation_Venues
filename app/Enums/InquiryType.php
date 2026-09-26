<?php

namespace App\Enums;

enum InquiryType: string
{
    case Package = 'package';
    case Rental = 'rental';
    case Visa = 'visa';
    case Baggage = 'baggage';
    case General = 'general';
    case Property = 'property';

    public function label(): string
    {
        return match ($this) {
            self::Package => 'Package',
            self::Rental => 'Vehicle rental',
            self::Visa => 'Visa',
            self::Baggage => 'Baggage',
            self::General => 'General',
            self::Property => 'Villa / house',
        };
    }
}
