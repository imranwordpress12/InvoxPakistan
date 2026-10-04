<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
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
        $statistics = $this->statistics($request);

        return view('company.dashboard', [
            'subscription' => $company?->latestSubscription,
            ...$statistics,
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        return response()->json($this->statistics($request))
            ->header('Cache-Control', 'private, no-store');
    }

    /**
     * Calculate submission statistics by the date the invoice was sent to FBR,
     * rather than the invoice's issue date.
     *
     * @return array{
     *     dateFrom: Carbon,
     *     dateTo: Carbon,
     *     totalStats: array{count: int, total_amount: float|int, total_excl_st: float|int, total_sales_tax: float|int},
     *     successfulStats: array{count: int, total_amount: float|int, total_excl_st: float|int, total_sales_tax: float|int},
     *     failedStats: array{count: int, total_amount: float|int, total_excl_st: float|int, total_sales_tax: float|int},
     *     dailyStatus: \Illuminate\Support\Collection,
     *     dailyAmounts: \Illuminate\Support\Collection
     * }
     */
    private function statistics(Request $request): array
    {
        $dateFrom = $request->date('date_from')?->startOfDay() ?? now()->subDays(6)->startOfDay();
        $dateTo = $request->date('date_to')?->endOfDay() ?? now()->endOfDay();

        $submitted = Invoice::query()
            ->where('company_id', $request->user()->company_id)
            ->whereIn('status', [Invoice::STATUS_SUBMITTED, Invoice::STATUS_SUCCESSFUL, Invoice::STATUS_FAILED])
            ->whereBetween('submitted_at', [$dateFrom, $dateTo])
            ->get();

        $successful = $submitted->where('status', Invoice::STATUS_SUCCESSFUL);
        $failed = $submitted->where('status', Invoice::STATUS_FAILED);

        $summarize = fn (Collection $invoices): array => [
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
            $onDate = $submitted->filter(fn ($invoice) => $invoice->submitted_at->toDateString() === $date);

            return [
                'date' => Carbon::parse($date)->format('d M'),
                'successful' => $onDate->where('status', Invoice::STATUS_SUCCESSFUL)->count(),
                'failed' => $onDate->where('status', Invoice::STATUS_FAILED)->count(),
            ];
        });

        $dailyAmounts = $days->map(function ($date) use ($submitted) {
            $onDate = $submitted->filter(fn ($invoice) => $invoice->submitted_at->toDateString() === $date);

            return [
                'date' => Carbon::parse($date)->format('d M'),
                'successful_amount' => (float) $onDate->where('status', Invoice::STATUS_SUCCESSFUL)->sum('total_amount'),
                'failed_amount' => (float) $onDate->where('status', Invoice::STATUS_FAILED)->sum('total_amount'),
            ];
        });

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalStats' => $summarize($submitted),
            'successfulStats' => $summarize($successful),
            'failedStats' => $summarize($failed),
            'dailyStatus' => $dailyStatus,
            'dailyAmounts' => $dailyAmounts,
        ];
    }
}
