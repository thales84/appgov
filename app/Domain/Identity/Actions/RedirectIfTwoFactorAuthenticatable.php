<?php

namespace App\Domain\Identity\Actions;

use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as FortifyRedirect;

class RedirectIfTwoFactorAuthenticatable extends FortifyRedirect
{
    protected function twoFactorChallengeResponse($request, $user)
    {
        $request->session()->put('login.portal', $request->input('portal', 'citizen'));

        return parent::twoFactorChallengeResponse($request, $user);
    }
}
