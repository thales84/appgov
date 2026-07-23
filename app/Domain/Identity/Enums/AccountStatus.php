<?php

namespace App\Domain\Identity\Enums;

enum AccountStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
