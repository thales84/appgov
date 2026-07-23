<?php

namespace App\Domain\Applications\Enums;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case CorrectionRequested = 'correction_requested';
    case Processing = 'processing';
    case DecisionPending = 'decision_pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case InProduction = 'in_production';
    case Available = 'available';
    case Delivered = 'delivered';
    case Closed = 'closed';
    case AwaitingPayment = 'awaiting_payment';
    case Scheduled = 'scheduled';
    case Cancelled = 'cancelled';
    case OnHold = 'on_hold';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::Draft => $locale === 'en' ? 'Draft' : 'Brouillon',
            self::Submitted => $locale === 'en' ? 'Submitted' : 'Dossier déposé',
            self::UnderReview => $locale === 'en' ? 'Under Review' : 'En instruction',
            self::CorrectionRequested => $locale === 'en' ? 'Correction Requested' : 'Correction demandée',
            self::Processing => $locale === 'en' ? 'Processing' : 'En traitement',
            self::DecisionPending => $locale === 'en' ? 'Decision Pending' : 'Décision en attente',
            self::Approved => $locale === 'en' ? 'Approved' : 'Approuvé',
            self::Rejected => $locale === 'en' ? 'Rejected' : 'Rejeté',
            self::InProduction => $locale === 'en' ? 'In Production' : 'En production',
            self::Available => $locale === 'en' ? 'Available' : 'Disponible',
            self::Delivered => $locale === 'en' ? 'Delivered' : 'Remis',
            self::Closed => $locale === 'en' ? 'Closed' : 'Clôturé',
            self::AwaitingPayment => $locale === 'en' ? 'Awaiting Payment' : 'En attente de paiement',
            self::Scheduled => $locale === 'en' ? 'Scheduled' : 'Programmé',
            self::Cancelled => $locale === 'en' ? 'Cancelled' : 'Annulé',
            self::OnHold => $locale === 'en' ? 'On Hold' : 'En pause',
        };
    }
}
