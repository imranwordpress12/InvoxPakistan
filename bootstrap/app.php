<?php

use App\Http\Middleware\CheckCompanySubscription;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsCompany;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // PRD #39: admin routes use `auth`+`admin`; company routes use
        // `auth`+`company`+`subscription` for the "main application"
        // routes (subscription-required and logout stay reachable with
        // just `auth`+`company` — see routes/company.php).
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'company' => EnsureUserIsCompany::class,
            'subscription' => CheckCompanySubscription::class,
        ]);

        // There is no single shared /login route (PRD #5: separate
        // /admin/login and /company/login areas), so an unauthenticated
        // visit to a protected route must bounce to whichever portal it
        // came from rather than a route named "login" that doesn't exist.
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('admin/*')
                ? route('admin.login')
                : route('company.login')
        );

        // Likewise, an already-authenticated user hitting a guest-only page
        // (either login form) is sent to their own dashboard, based on who
        // they actually are rather than which URL they happened to visit.
        $middleware->redirectUsersTo(
            fn (Request $request) => $request->user()?->isAdmin()
                ? route('admin.dashboard')
                : route('company.dashboard')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
