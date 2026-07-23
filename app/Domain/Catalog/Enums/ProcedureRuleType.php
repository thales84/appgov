<?php

namespace App\Domain\Catalog\Enums;

enum ProcedureRuleType: string
{
    case Eligibility = 'eligibility';
    case Validation = 'validation';
    case Transition = 'transition';
}
