<?php

namespace App\Domain\Payments\Enums;

enum InvoiceStatus: string
{
    case Unpaid = 'unpaid';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::Unpaid => $locale === 'en' ? 'Unpaid' : 'Non payée',
            self::PartiallyPaid => $locale === 'en' ? 'Partially Paid' : 'Partiellement payée',
            self::Paid => $locale === 'en' ? 'Paid' : 'Payée',
            self::Cancelled => $locale === 'en' ? 'Cancelled' : 'Annulée',
        };
    }
}
