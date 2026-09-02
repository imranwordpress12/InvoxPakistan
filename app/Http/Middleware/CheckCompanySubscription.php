<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PRD #10/#11 Company Access Rule, enforced as middleware: a company user
 * may reach the routes behind this only while their subscription is
 * active() right now — Phase 5's Subscription::isActive(), which already
 * checks both the stored `status` AND that `ends_at` hasn't lapsed, so a
 * stale "active" row (Phase 11's scheduler hasn't caught up yet) is
 * blocked here exactly as correctly as a genuinely `pending` one.
 *
 * Runs after `auth`+`company` in the middleware stack (PRD #39), so by the
 * time this executes we already know we have an authenticated company
 * user — never a request-supplied company ID (PRD #41).
 */
class CheckCompanySubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $company = $request->user()?->load('company.latestSubscription.latestTransaction')->company;

        if ($company?->latestSubscription?->latestTransaction?->due_at?->isAfter(today())) {
            return $next($request);
        }

        return redirect()->route('company.subscription-required');
    }
}
