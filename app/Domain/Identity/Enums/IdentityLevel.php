<?php

namespace App\Domain\Identity\Enums;

enum IdentityLevel: string
{
    case Unverified = 'unverified';
    case EmailVerified = 'email_verified';
    case IdentityDeclared = 'identity_declared';
    case IdentityChecked = 'identity_checked';
}
