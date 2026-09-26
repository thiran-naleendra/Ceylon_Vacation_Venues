<?php

namespace App\Enums;

enum VehicleAvailability: string
{
    case Available = 'available';
    case OnRequest = 'on_request';
    case Unavailable = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available', self::OnRequest => 'On request', self::Unavailable => 'Unavailable',
        };
    }
}
