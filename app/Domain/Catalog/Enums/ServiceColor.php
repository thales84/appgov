<?php

namespace App\Domain\Catalog\Enums;

enum ServiceColor: string
{
    case Mobility = 'mobility';
    case Immigration = 'immigration';
    case Nationality = 'nationality';
    case Education = 'education';
    case Civil = 'civil';
    case Business = 'business';
}
