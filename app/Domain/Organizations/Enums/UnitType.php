<?php

namespace App\Domain\Organizations\Enums;

enum UnitType: string
{
    case Directorate = 'directorate';
    case Delegation = 'delegation';
    case Center = 'center';
    case ServiceDesk = 'service_desk';
}
