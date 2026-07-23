<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Fortify;

class AgentSecurityController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $hasSecret = filled($user->two_factor_secret);
        $isConfirmed = $user->hasEnabledTwoFactorAuthentication();

        return Inertia::render('Agent/Security', [
            'twoFactor' => [
                'hasSecret' => $hasSecret,
                'confirmed' => $isConfirmed,
                'qrCodeSvg' => $hasSecret && ! $isConfirmed ? $user->twoFactorQrCodeSvg() : null,
                'manualKey' => $hasSecret && ! $isConfirmed
                    ? Fortify::currentEncrypter()->decrypt($user->two_factor_secret)
                    : null,
                'recoveryCodes' => $isConfirmed ? $user->recoveryCodes() : [],
            ],
        ]);
    }
}
