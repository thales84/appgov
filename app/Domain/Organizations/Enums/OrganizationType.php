<?php

namespace App\Domain\Organizations\Enums;

enum OrganizationType: string
{
    case Ministry = 'ministry';
    case PublicAgency = 'public_agency';
    case PublicPartner = 'public_partner';
}
