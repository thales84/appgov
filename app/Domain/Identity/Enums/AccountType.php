<?php

namespace App\Domain\Identity\Enums;

enum AccountType: string
{
    case Citizen = 'citizen';
    case Agent = 'agent';
}
