<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
                'displayTimezone' => config('appgov.display_timezone'),
            ],
            'locale' => app()->getLocale(),
            'auth' => [
                'user' => fn () => $request->user() ? [
                    'publicId' => $request->user()->public_id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'accountType' => $request->user()->account_type->value,
                    'emailVerified' => $request->user()->hasVerifiedEmail(),
                    'permissions' => $request->user()->getPermissionNames()->values(),
                ] : null,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
        ];
    }
}
