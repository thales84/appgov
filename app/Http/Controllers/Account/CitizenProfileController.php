<?php

namespace App\Http\Controllers\Account;

use App\Domain\Identity\Actions\UpdateCitizenProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCitizenProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CitizenProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user()->load('citizenProfile');

        return Inertia::render('Account/Profile', [
            'profile' => [
                'firstName' => $user->citizenProfile->first_name,
                'lastName' => $user->citizenProfile->last_name,
                'email' => $user->email,
                'phone' => $user->citizenProfile->phone,
                'preferredLocale' => $user->citizenProfile->preferred_locale,
                'identityLevel' => $user->citizenProfile->identity_level->value,
            ],
        ]);
    }

    public function update(
        UpdateCitizenProfileRequest $request,
        UpdateCitizenProfile $action
    ): RedirectResponse {
        $action->handle($request->user(), $request->validated());

        return back()->with('status', 'citizen-profile-updated');
    }
}
