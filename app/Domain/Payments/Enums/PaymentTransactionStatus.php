<?php

namespace App\Domain\Payments\Enums;

enum PaymentTransactionStatus: string
{
    case Initiated = 'initiated';
    case Pending = 'pending';
    case Successful = 'successful';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::Initiated => $locale === 'en' ? 'Initiated' : 'Initiée',
            self::Pending => $locale === 'en' ? 'Pending' : 'En attente',
            self::Successful => $locale === 'en' ? 'Successful' : 'Réussie',
            self::Failed => $locale === 'en' ? 'Failed' : 'Échouée',
            self::Cancelled => $locale === 'en' ? 'Cancelled' : 'Annulée',
        };
    }
}
