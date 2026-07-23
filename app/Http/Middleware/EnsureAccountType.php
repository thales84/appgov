<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountType
{
    public function handle(Request $request, Closure $next, string $accountType): Response
    {
        $user = $request->user();

        abort_unless(
            $user && $user->isActive() && $user->account_type->value === $accountType,
            Response::HTTP_FORBIDDEN
        );

        return $next($request);
    }
}
