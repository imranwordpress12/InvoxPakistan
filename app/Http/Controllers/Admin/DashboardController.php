<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Transaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * The Admin Dashboard (PRD #13): summary cards, a companies overview,
     * and recent payments.
     */
    public function index(): View
    {
        $totalCompanies = Company::count();

        // "Paid" / "Pending" here means the same thing Phase 7's access
        // middleware and Phase 5's dashboard warning already mean: is the
        // company's current subscription active *right now* (status AND
        // ends_at), not just what the stored status column happens to say.
        $paidCompanies = Company::whereHas('latestSubscription', fn ($q) => $q->currentlyActive())->count();
        $pendingCompanies = $totalCompanies - $paidCompanies;

        $companiesOverview = Company::with('latestSubscription')
            ->latest()
            ->limit(10)
            ->get();

        $recentPayments = Transaction::with('company')
            ->whereNotNull('paid_at')
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'totalCompanies' => $totalCompanies,
            'paidCompanies' => $paidCompanies,
            'pendingCompanies' => $pendingCompanies,
            'companiesOverview' => $companiesOverview,
            'recentPayments' => $recentPayments,
        ]);
    }
}
