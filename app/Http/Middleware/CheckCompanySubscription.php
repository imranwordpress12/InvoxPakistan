<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use App\Models\Transaction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces Invoice Restriction (rules.md #19):
 * Invoice access is blocked when:
 * 1. Subscription is Inactive or missing.
 * 2. Latest unpaid transaction is Overdue.
 * Unblocks when Overdue is paid or subscription becomes Active.
 */
class CheckCompanySubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->company_id) {
            return redirect()->route('company.subscription-required');
        }

        $company = $user->company()->with('latestSubscription')->first();
        if (! $company) {
            return redirect()->route('company.subscription-required');
        }

        $subscription = $company->latestSubscription;

        // Condition 1: Blocked if subscription is missing or Inactive
        if (! $subscription || ! $subscription->isActive()) {
            return redirect()->route('company.subscription-required');
        }

        // Condition 2: Blocked if latest unpaid transaction is Overdue
        $latestUnpaidTransaction = Transaction::where('company_id', $company->id)
            ->whereIn('status', [Transaction::STATUS_DUE, Transaction::STATUS_PENDING, Transaction::STATUS_OVERDUE])
            ->latest('id')
            ->first();

        if ($latestUnpaidTransaction && $latestUnpaidTransaction->status === Transaction::STATUS_OVERDUE) {
            return redirect()->route('company.subscription-required');
        }

        return $next($request);
    }
}
