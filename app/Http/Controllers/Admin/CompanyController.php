<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditLogger;
use App\Domain\Companies\CompanyOnboardingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Models\Company;
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
            $query->whereHas('latestSubscription', fn ($q) => $q->where('status', $status));
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

        return view('admin.companies.create');
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
            'transactions' => fn ($q) => $q->latest()->limit(10),
            'users',
        ]);

        return view('admin.companies.show', ['company' => $company]);
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        $company->load(['users']);

        return view('admin.companies.edit', ['company' => $company]);
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

            $company->update([
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
                'fbr_token_production' => $data['fbr_token_production'] ?? $company->fbr_token_production,
                'fbr_token_sandbox' => $data['fbr_token_sandbox'] ?? $company->fbr_token_sandbox,
                'status' => $data['status'],
            ]);

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
                // Login email is tracked alongside company fields since
                // it's ordinary business data, not a credential — the
                // password itself never reaches here regardless of
                // whether it was changed (PRD #33/#54).
                old: [...$originalCompanyValues, 'user_email' => $originalLoginEmail],
                new: [...$company->only($companyFields), 'user_email' => $loginUser?->email],
            );

            // Leaving either field blank keeps that credential unchanged —
            // there's no "clear" control in this form (see edit view).
            $fbrValues = array_filter([
                'fbr_token_production' => $data['fbr_token_production'] ?? null,
                'fbr_token_sandbox' => $data['fbr_token_sandbox'] ?? null,
            ]);

            if ($fbrValues !== []) {
                $company->update($fbrValues);

                // Deliberately no old/new values here — the fact that FBR
                // credentials changed is logged, never their contents
                // (PRD #21/#33/#54).
                AuditLogger::log(
                    action: 'fbr_credentials.updated',
                    module: 'companies',
                    description: "FBR credentials updated for \"{$company->name}\".",
                    company: $company,
                );
            }
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

        $transactions = $company->transactions()->latest()->paginate(20);

        return view('admin.companies.transactions', [
            'company' => $company,
            'transactions' => $transactions,
        ]);
    }
}
