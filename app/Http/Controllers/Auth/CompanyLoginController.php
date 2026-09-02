<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompanyLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompanyLoginController extends Controller
{
    /**
     * Display the company login form.
     */
    public function create(): View
    {
        return view('auth.company-login');
    }

    /**
     * Handle a company authentication attempt.
     *
     * Subscription status is intentionally not checked here — that's the
     * `subscription` middleware's job (Phase 7), applied to the protected
     * company routes rather than the login step itself.
     */
    public function store(CompanyLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        AuditLogger::log(
            action: 'company.login',
            module: 'auth',
            description: Auth::user()->name.' logged in.',
            company: Auth::user()->company,
        );

        return redirect()->intended(route('company.dashboard'));
    }

    /**
     * Log the company user out.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('company.login');
    }
}
