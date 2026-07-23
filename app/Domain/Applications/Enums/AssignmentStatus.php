<?php

namespace App\Domain\Applications\Enums;

enum AssignmentStatus: string
{
    case Active = 'active';
    case Transferred = 'transferred';
    case Closed = 'closed';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::Active => $locale === 'en' ? 'Active' : 'Active',
            self::Transferred => $locale === 'en' ? 'Transferred' : 'Transférée',
            self::Closed => $locale === 'en' ? 'Closed' : 'Clôturée',
        };
    }
}
