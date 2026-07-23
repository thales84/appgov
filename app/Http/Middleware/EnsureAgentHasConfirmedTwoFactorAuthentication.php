<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgentHasConfirmedTwoFactorAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('agent.security');
        }

        return $next($request);
    }
}
