<?php

namespace App\Domain\Applications\Enums;

enum DocumentStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Invalid = 'invalid';
    case Expired = 'expired';
    case Replaced = 'replaced';
    case Quarantined = 'quarantined';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::Pending => $locale === 'en' ? 'Pending' : 'En attente',
            self::Valid => $locale === 'en' ? 'Valid' : 'Valide',
            self::Invalid => $locale === 'en' ? 'Invalid' : 'Non valide',
            self::Expired => $locale === 'en' ? 'Expired' : 'Expiré',
            self::Replaced => $locale === 'en' ? 'Replaced' : 'Remplacé',
            self::Quarantined => $locale === 'en' ? 'Quarantined' : 'En quarantaine',
        };
    }
}
