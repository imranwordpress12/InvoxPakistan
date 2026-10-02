<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Domain\Companies\CompanyOnboardingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\Province;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * The companies listing (PRD #14/#15), with search + filters (PRD #59).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Company::class);

        $query = Company::query()->with('latestSubscription');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($type = $request->string('subscription_type')->value()) {
            $query->whereHas('latestSubscription', fn ($q) => $q->where('type', $type));
        }

        if ($status = $request->string('subscription_status')->value()) {
            if ($status === 'currently_active') {
                $query->whereHas('latestSubscription', fn ($q) => $q->currentlyActive());
            } else {
                $query->whereHas('latestSubscription', fn ($q) => $q->where('status', $status));
            }
        }

        $companies = $query->latest()->paginate(20)->withQueryString();

        return view('admin.companies.index', [
            'companies' => $companies,
            'totalCompanies' => Company::count(),
        ]);
    }

    /**
     * Pending Companies (PRD #16): NOT a separate entity — a filtered view
     * of companies whose current subscription isn't active *right now*.
     * Uses the exact same predicate as the admin dashboard's "Pending
     * Companies" summary card (Phase 8's Subscription::scopeCurrentlyActive()),
     * so the two numbers always agree.
     */
    public function pending(Request $request): View
    {
        $this->authorize('viewAny', Company::class);

        $query = Company::query()
            ->whereDoesntHave('latestSubscription', fn ($q) => $q->currentlyActive())
            ->with(['latestSubscription' => function ($q) {
                $q->with(['transactions' => fn ($tq) => $tq->where('status', Transaction::STATUS_PENDING)]);
            }]);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Only matches companies that actually have a pending invoice —
        // a company that's "pending" for some other reason (e.g. it hasn't
        // been through a Phase 11 scheduler run yet) has no due date to
        // filter on, so it's correctly excluded rather than shown with a
        // misleading date.
        if ($dueBy = $request->string('due_by')->value()) {
            $query->whereHas(
                'latestSubscription.transactions',
                fn ($q) => $q->where('status', Transaction::STATUS_PENDING)->whereDate('due_at', '<=', $dueBy)
            );
        }

        $companies = $query->latest()->paginate(20)->withQueryString();

        return view('admin.companies.pending', ['companies' => $companies]);
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('admin.companies.create', [
            'provinces' => Province::orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Create Company -> Create User -> Create Subscription -> Create
     * Transaction, atomically (PRD #20/#52) — see CompanyOnboardingService.
     */
    public function store(StoreCompanyRequest $request, CompanyOnboardingService $onboarding): RedirectResponse
    {
        $company = $onboarding->onboard($request->validated());

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('status', "Company \"{$company->name}\" created.");
    }

    /**
     * Company details: company info, current subscription, subscription
     * history, and recent transaction history (PRD #23).
     */
    public function show(Company $company): View
    {
        $this->authorize('view', $company);

        $company->load([
            'latestSubscription',
            'subscriptions' => fn ($q) => $q->latest(),
            'transactions' => fn ($q) => $q->latest()->limit(6),
            'users',
        ]);
        $company->setRelation('transactions', $company->transactions->sortBy('due_at')->values());

        return view('admin.companies.show', ['company' => $company]);
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        $company->load(['users']);

        return view('admin.companies.edit', [
            'company' => $company,
            'provinces' => Province::orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Updates company information, login information, and FBR credentials.
     * Subscription fields are intentionally not editable here — see
     * UpdateCompanyRequest's docblock.
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $data = $request->validated();

        $companyFields = [
            'name', 'business_name', 'email', 'phone', 'address',
            'city', 'province', 'country', 'ntn_cnic', 'business_registration_number', 'status',
            'fbr_token_production', 'fbr_token_sandbox',
        ];

        DB::transaction(function () use ($data, $company, $companyFields) {
            $originalCompanyValues = $company->only($companyFields);
            $loginUser = $company->users()->first();
            $originalLoginEmail = $loginUser?->email;

            $updateData = [
                'name' => $data['name'],
                'business_name' => $data['business_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'province' => $data['province'] ?? null,
                'country' => $data['country'] ?? null,
                'ntn_cnic' => $data['ntn_cnic'] ?? null,
                'business_registration_number' => $data['business_registration_number'] ?? null,
                'status' => $data['status'],
            ];

            if (isset($data['fbr_status'])) {
                $updateData['fbr_status'] = $data['fbr_status'];
            }
            if (filled($data['fbr_token_production'] ?? null)) {
                $updateData['fbr_token_production'] = $data['fbr_token_production'];
            }
            if (filled($data['fbr_token_sandbox'] ?? null)) {
                $updateData['fbr_token_sandbox'] = $data['fbr_token_sandbox'];
            }

            $company->update($updateData);

            // Subscription Management (Chunk 1)
            $latestSub = $company->latestSubscription;
            $newSubStatus = $data['subscription_status'] ?? null;
            $newSubStartsAt = isset($data['subscription_starts_at']) && filled($data['subscription_starts_at'])
                ? \Illuminate\Support\Carbon::parse($data['subscription_starts_at'])->startOfDay()
                : null;
            $newSubType = $data['subscription_type'] ?? ($latestSub?->type ?? \App\Models\Subscription::TYPE_MONTHLY);
            $newAmount = isset($data['amount']) ? (float) $data['amount'] : ($latestSub?->amount ?? 0.00);

            if ($newSubStatus === \App\Models\Subscription::STATUS_ACTIVE) {
                if (! $latestSub || $latestSub->status !== \App\Models\Subscription::STATUS_ACTIVE) {
                    // New Activation or Inactive -> Active Reactivation
                    $startsAt = $newSubStartsAt ?? now()->startOfDay();
                    $endsAt = \App\Domain\Subscriptions\SubscriptionPeriod::endDateFor($newSubType, $startsAt);

                    $subscription = $company->subscriptions()->create([
                        'type' => $newSubType,
                        'status' => \App\Models\Subscription::STATUS_ACTIVE,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'amount' => $newAmount,
                    ]);

                    // Transaction #1: Initial Paid transaction (Rule 6 & 20)
                    $company->transactions()->create([
                        'subscription_id' => $subscription->id,
                        'invoice_number' => \App\Domain\Transactions\InvoiceNumberGenerator::generate(),
                        'transaction_type' => \App\Models\Transaction::TYPE_INITIAL,
                        'subscription_type' => $newSubType,
                        'amount' => $newAmount,
                        'status' => \App\Models\Transaction::STATUS_PAID,
                        'billing_period_start' => $startsAt,
                        'billing_period_end' => $endsAt,
                        'due_at' => $startsAt,
                        'paid_at' => now(),
                        'notes' => null,
                    ]);

                    // Transaction #2: Single future transaction (Rule 6 & 20)
                    $tx2Start = $endsAt->copy()->addDay()->startOfDay();
                    $tx2End = \App\Domain\Subscriptions\SubscriptionPeriod::endDateFor($newSubType, $tx2Start);
                    $tx2Status = $tx2End->isPast() ? \App\Models\Transaction::STATUS_OVERDUE : \App\Models\Transaction::STATUS_DUE;

                    $company->transactions()->create([
                        'subscription_id' => $subscription->id,
                        'invoice_number' => \App\Domain\Transactions\InvoiceNumberGenerator::generate(),
                        'transaction_type' => \App\Models\Transaction::TYPE_RENEWAL,
                        'subscription_type' => $newSubType,
                        'amount' => $newAmount,
                        'status' => $tx2Status,
                        'billing_period_start' => $tx2Start,
                        'billing_period_end' => $tx2End,
                        'due_at' => $tx2End,
                        'paid_at' => null,
                        'notes' => null,
                    ]);
                } else {
                    // Already Active Subscription: check for Subscription Type change
                    if ($latestSub->type !== $newSubType) {
                        $endsAt = \App\Domain\Subscriptions\SubscriptionPeriod::endDateFor($newSubType, $latestSub->starts_at);
                        $latestSub->update([
                            'type' => $newSubType,
                            'amount' => $newAmount,
                            'ends_at' => $endsAt,
                        ]);

                        // Recalculate dates for current/future unpaid transactions (Rule 21)
                        // Pending -> Due, Overdue -> Due, Due -> Due. Keep billing_period_start unchanged.
                        $unpaidTxs = \App\Models\Transaction::where('company_id', $company->id)
                            ->whereIn('status', [\App\Models\Transaction::STATUS_DUE, \App\Models\Transaction::STATUS_PENDING, 'overdue'])
                            ->get();

                        foreach ($unpaidTxs as $tx) {
                            $newEnd = \App\Domain\Transactions\TransactionBillingPeriod::endDateFor($newSubType, $tx->billing_period_start);
                            $tx->update([
                                'subscription_type' => $newSubType,
                                'amount' => $newAmount,
                                'billing_period_end' => $newEnd,
                                'due_at' => $newEnd,
                                'status' => \App\Models\Transaction::STATUS_DUE,
                            ]);
                        }
                    } else {
                        // Type unchanged: update amount if needed
                        $latestSub->update(['amount' => $newAmount]);
                        \App\Models\Transaction::where('company_id', $company->id)
                            ->whereIn('status', [\App\Models\Transaction::STATUS_DUE, \App\Models\Transaction::STATUS_PENDING])
                            ->update(['amount' => $newAmount]);
                    }
                }
            } elseif ($newSubStatus === \App\Models\Subscription::STATUS_INACTIVE) {
                if ($latestSub && $latestSub->status === \App\Models\Subscription::STATUS_ACTIVE) {
                    $latestSub->update(['status' => \App\Models\Subscription::STATUS_INACTIVE]);
                }
                // Rule 18 & 33: Delete future/unpaid transactions that are Due or Pending when subscription becomes Inactive.
                // NEVER delete Paid or Overdue transactions!
                \App\Models\Transaction::where('company_id', $company->id)
                    ->whereIn('status', [\App\Models\Transaction::STATUS_DUE, \App\Models\Transaction::STATUS_PENDING])
                    ->delete();
            }

            if ($loginUser) {
                $loginUser->email = $data['user_email'];

                if (! empty($data['password'])) {
                    $loginUser->password = $data['password'];
                }

                $loginUser->save();
            }

            AuditLogger::log(
                action: 'company.updated',
                module: 'companies',
                description: "Company \"{$company->name}\" updated.",
                company: $company,
                old: [...$originalCompanyValues, 'user_email' => $originalLoginEmail],
                new: [...$company->only($companyFields), 'user_email' => $loginUser?->email],
            );
        });

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('status', "Company \"{$company->name}\" updated.");
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        $name = $company->name;

        $company->delete();

        AuditLogger::log(
            action: 'company.deleted',
            module: 'companies',
            description: "Company \"{$name}\" deleted.",
            company: $company,
        );

        return redirect()
            ->route('admin.companies.index')
            ->with('status', "Company \"{$name}\" deleted.");
    }

    /**
     * Full transaction history for one company (PRD #22/#38). Read-only —
     * marking a pending transaction as paid is Phase 6.
     */
    public function transactions(Company $company): View
    {
        $this->authorize('view', $company);

        $transactions = $company->transactions()->orderBy('due_at')->orderBy('id')->paginate(20);

        return view('admin.companies.transactions', [
            'company' => $company,
            'transactions' => $transactions,
        ]);
    }
}
