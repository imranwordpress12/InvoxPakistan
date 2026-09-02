<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Server-side role gate for the company area (PRD #39/#40). This only
 * confirms the authenticated user is a company account — it deliberately
 * does not check subscription status; that's CheckCompanySubscription,
 * added in Phase 7.
 */
class EnsureUserIsCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isCompany()) {
            abort(403);
        }

        return $next($request);
    }
}
