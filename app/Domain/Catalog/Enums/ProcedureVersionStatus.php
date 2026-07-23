<?php

namespace App\Domain\Catalog\Enums;

enum ProcedureVersionStatus: string
{
    case Draft = 'draft';
    case UnderReview = 'under_review';
    case Published = 'published';
    case Retired = 'retired';
}
