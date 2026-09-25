<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Booked = 'booked';
    case Diagnostics = 'diagnostics';
    case WaitingForParts = 'waiting_for_parts';
    case Ready = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Booked => 'Rezervets',
            self::Diagnostics => 'Diagnostika',
            self::WaitingForParts => 'Gaida detalu',
            self::Ready => 'Gatavs',
            self::Completed => 'Pabeigts',
            self::Cancelled => 'Atcelts',
        };
    }
}
