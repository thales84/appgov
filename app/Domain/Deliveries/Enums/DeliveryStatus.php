<?php

namespace App\Domain\Deliveries\Enums;

enum DeliveryStatus: string
{
    case Dispatched = 'dispatched';
    case Available = 'available';
    case Delivered = 'delivered';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::Dispatched => $locale === 'en' ? 'Dispatched' : 'Expédié vers le guichet',
            self::Available => $locale === 'en' ? 'Available for Pickup' : 'Disponible au guichet',
            self::Delivered => $locale === 'en' ? 'Delivered' : 'Remis à l\'usager',
        };
    }
}
