<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionRequiredController extends Controller
{
    /**
     * The page a company user is redirected to when CheckCompanySubscription
     * blocks access (PRD #10). Deliberately not behind the `subscription`
     * middleware itself — that would be an infinite redirect loop.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $company = $request->user()->load('company.latestSubscription')->company;
        $subscription = $company?->latestSubscription;

        // An active company landing here (e.g. an old bookmark from before
        // they renewed) belongs on the dashboard instead.
        if ($subscription?->isActive()) {
            return redirect()->route('company.dashboard');
        }

        return view('company.subscription-required', ['subscription' => $subscription]);
    }
}
