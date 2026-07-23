<?php

namespace App\Domain\Decisions\Enums;

enum DecisionType: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Adjourned = 'adjourned';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::Approved => $locale === 'en' ? 'Approved' : 'Favorable / Approuvé',
            self::Rejected => $locale === 'en' ? 'Rejected' : 'Défavorable / Rejeté',
            self::Adjourned => $locale === 'en' ? 'Adjourned' : 'Ajourné',
        };
    }
}
