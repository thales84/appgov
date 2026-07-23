<?php

use App\Http\Middleware\EnsureAccountType;
use App\Http\Middleware\EnsureAgentHasActiveAssignment;
use App\Http\Middleware\EnsureAgentHasConfirmedTwoFactorAuthentication;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Sentry\Laravel\Integration;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/health',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('agent/*') || $request->is('admin/*')
                ? route('agent.login')
                : route('login')
        );
        $middleware->redirectUsersTo(
            fn (Request $request) => $request->user()?->isAgent()
                ? route('agent.dashboard')
                : route('account.dashboard')
        );

        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'account.type' => EnsureAccountType::class,
            'agent.assignment' => EnsureAgentHasActiveAssignment::class,
            'agent.2fa' => EnsureAgentHasConfirmedTwoFactorAuthentication::class,
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);

        $exceptions->respond(function (Response $response, Throwable $_exception, Request $request): Response {
            if (
                $response->getStatusCode() === Response::HTTP_FORBIDDEN
                && ! $request->expectsJson()
                && ! $request->is('api/*')
            ) {
                return Inertia::render('Errors/Forbidden')
                    ->toResponse($request)
                    ->setStatusCode(Response::HTTP_FORBIDDEN);
            }

            return $response;
        });
    })->create();
