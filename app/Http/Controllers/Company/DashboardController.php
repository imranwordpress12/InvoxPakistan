<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Company Dashboard PRD #4: invoice submission stats + daily charts,
     * scoped to the authenticated company only (never a request-supplied
     * ID — PRD #18). Draft invoices are excluded from every count/total
     * here — the heading is "Stats of Invoices Submitted to FBR", and a
     * draft was never actually submitted.
     *
     * The subscription expiry warning (built for the original
     * Subscription/Payment System PRD) is kept as a small banner at the
     * top rather than removed — see CHANGELOG_PROJECT.md for why the
     * fuller subscription details table was dropped from this page.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load('company.latestSubscription');
        $company = $user->company;
        $subscription = $company?->latestSubscription;

        $dateFrom = $request->date('date_from')?->startOfDay() ?? now()->subDays(6)->startOfDay();
        $dateTo = $request->date('date_to')?->endOfDay() ?? now()->endOfDay();

        $submitted = Invoice::query()
            ->where('company_id', $company?->id)
            ->whereIn('status', [Invoice::STATUS_SUBMITTED, Invoice::STATUS_SUCCESSFUL, Invoice::STATUS_FAILED])
            ->whereBetween('invoice_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->get();

        $successful = $submitted->where('status', Invoice::STATUS_SUCCESSFUL);
        $failed = $submitted->where('status', Invoice::STATUS_FAILED);

        $summarize = fn ($invoices) => [
            'count' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'total_excl_st' => $invoices->sum('total_excl_st'),
            'total_sales_tax' => $invoices->sum('total_sales_tax'),
        ];

        $days = collect();
        for ($date = $dateFrom->copy(); $date->lte($dateTo); $date->addDay()) {
            $days->push($date->toDateString());
        }

        $dailyStatus = $days->map(function ($date) use ($submitted) {
            $onDate = $submitted->filter(fn ($invoice) => $invoice->invoice_date->toDateString() === $date);

            return [
                'date' => Carbon::parse($date)->format('d M'),
                'successful' => $onDate->where('status', Invoice::STATUS_SUCCESSFUL)->count(),
                'failed' => $onDate->where('status', Invoice::STATUS_FAILED)->count(),
            ];
        });

        $dailyAmounts = $days->map(function ($date) use ($submitted) {
            $onDate = $submitted->filter(fn ($invoice) => $invoice->invoice_date->toDateString() === $date);

            return [
                'date' => Carbon::parse($date)->format('d M'),
                'successful_amount' => (float) $onDate->where('status', Invoice::STATUS_SUCCESSFUL)->sum('total_amount'),
                'failed_amount' => (float) $onDate->where('status', Invoice::STATUS_FAILED)->sum('total_amount'),
            ];
        });

        return view('company.dashboard', [
            'subscription' => $subscription,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalStats' => $summarize($submitted),
            'successfulStats' => $summarize($successful),
            'failedStats' => $summarize($failed),
            'dailyStatus' => $dailyStatus,
            'dailyAmounts' => $dailyAmounts,
        ]);
    }
}
