<?php

namespace App\Domain\Production\Enums;

enum ProductionOrderStatus: string
{
    case Queued = 'queued';
    case InProduction = 'in_production';
    case QualityChecked = 'quality_checked';
    case Rejected = 'rejected';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::Queued => $locale === 'en' ? 'Queued' : 'En attente d\'impression',
            self::InProduction => $locale === 'en' ? 'In Production' : 'En cours d\'impression / confection',
            self::QualityChecked => $locale === 'en' ? 'Quality Checked' : 'Contrôle qualité validé',
            self::Rejected => $locale === 'en' ? 'Rejected' : 'Rejeté en production',
        };
    }
}
