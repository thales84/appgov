<?php

namespace App\Domain\Organizations\Enums;

enum TerritoryType: string
{
    case Country = 'country';
    case Region = 'region';
    case Department = 'department';
    case Municipality = 'municipality';
}
