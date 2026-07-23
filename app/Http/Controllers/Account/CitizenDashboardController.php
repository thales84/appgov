<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CitizenDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $profile = $request->user()->citizenProfile;

        return Inertia::render('Account/Dashboard', [
            'identity' => [
                'level' => $profile->identity_level->value,
                'profileComplete' => filled($profile->phone),
            ],
        ]);
    }
}
