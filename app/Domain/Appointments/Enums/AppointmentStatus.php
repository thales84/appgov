<?php

namespace App\Domain\Appointments\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Present = 'present';
    case Absent = 'absent';
    case Cancelled = 'cancelled';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::Scheduled => $locale === 'en' ? 'Scheduled' : 'Programmé',
            self::Present => $locale === 'en' ? 'Present' : 'Présent',
            self::Absent => $locale === 'en' ? 'Absent' : 'Absent',
            self::Cancelled => $locale === 'en' ? 'Cancelled' : 'Annulé',
        };
    }
}
