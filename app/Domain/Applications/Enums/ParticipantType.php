<?php

namespace App\Domain\Applications\Enums;

enum ParticipantType: string
{
    case Applicant = 'applicant';
    case Beneficiary = 'beneficiary';
    case Representative = 'representative';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::Applicant => $locale === 'en' ? 'Applicant' : 'Demandeur',
            self::Beneficiary => $locale === 'en' ? 'Beneficiary' : 'Bénéficiaire',
            self::Representative => $locale === 'en' ? 'Representative' : 'Mandataire',
        };
    }
}
