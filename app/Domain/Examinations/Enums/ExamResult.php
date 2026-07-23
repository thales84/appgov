<?php

namespace App\Domain\Examinations\Enums;

enum ExamResult: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case Absent = 'absent';
    case Cancelled = 'cancelled';

    public function label(string $locale = 'fr'): string
    {
        return match ($this) {
            self::Passed => $locale === 'en' ? 'Passed' : 'Admis / Réussi',
            self::Failed => $locale === 'en' ? 'Failed' : 'Ajourné / Échoué',
            self::Absent => $locale === 'en' ? 'Absent' : 'Absent',
            self::Cancelled => $locale === 'en' ? 'Cancelled' : 'Annulé',
        };
    }
}
