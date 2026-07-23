<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;

class AuthenticationResponse implements LoginResponse, TwoFactorLoginResponse
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if ($user->isAgent()) {
            return redirect()->intended(
                $user->hasEnabledTwoFactorAuthentication()
                    ? route('agent.dashboard')
                    : route('agent.security')
            );
        }

        return redirect()->intended(route('account.dashboard'));
    }
}
