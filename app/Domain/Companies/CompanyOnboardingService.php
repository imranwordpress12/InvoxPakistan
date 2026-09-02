<?php

namespace App\Domain\Companies;

use App\Domain\Audit\AuditLogger;
use App\Domain\Subscriptions\SubscriptionPeriod;
use App\Domain\Transactions\InvoiceNumberGenerator;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Creates a company together with its first login user, its first
 * subscription, and its first (already-paid) transaction — all inside one
 * database transaction (PRD #20/#52): Company -> User -> Subscription ->
 * Transaction must succeed or fail together.
 */
class CompanyOnboardingService
{
    /**
     * @param  array<string, mixed>  $data  Validated StoreCompanyRequest data.
     */
    public function onboard(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            $company = Company::create([
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
                'fbr_token_production' => $data['fbr_token_production'] ?? null,
                'fbr_token_sandbox' => $data['fbr_token_sandbox'] ?? null,
                'status' => Company::STATUS_ACTIVE,
            ]);

            $company->users()->create([
                'name' => $data['name'],
                'email' => $data['user_email'],
                // Hashed automatically by User's 'password' => 'hashed' cast.
                'password' => $data['password'],
                'role' => User::ROLE_COMPANY,
            ]);

            $startsAt = isset($data['subscription_starts_at']) && $data['subscription_starts_at'] !== null
                ? Carbon::parse($data['subscription_starts_at'])->startOfDay()
                : now();

            $subscription = $company->subscriptions()->create([
                'type' => $data['subscription_type'],
                'status' => Subscription::STATUS_ACTIVE,
                'starts_at' => $startsAt,
                'ends_at' => SubscriptionPeriod::endDateFor($data['subscription_type'], $startsAt),
                'amount' => $data['amount'],
            ]);

            $registeredAt = now();
            $nextDueAt = $data['subscription_type'] === Subscription::TYPE_MONTHLY
                ? $registeredAt->copy()->addMonth()
                : $registeredAt->copy()->addYear();

            $company->transactions()->create([
                'subscription_id' => $subscription->id,
                'invoice_number' => InvoiceNumberGenerator::generate(),
                'transaction_type' => Transaction::TYPE_INITIAL,
                'subscription_type' => $data['subscription_type'],
                'amount' => $data['amount'],
                'status' => Transaction::STATUS_PAID,
                'billing_period_start' => $subscription->starts_at,
                'billing_period_end' => $subscription->ends_at,
                'due_at' => $registeredAt,
                'paid_at' => $registeredAt,
                'notes' => null,
            ]);

            $nextPeriodEnd = $data['subscription_type'] === Subscription::TYPE_MONTHLY
                ? $nextDueAt->copy()->addMonth()
                : $nextDueAt->copy()->addYear();

            $company->transactions()->create([
                'subscription_id' => $subscription->id,
                'invoice_number' => InvoiceNumberGenerator::generate(),
                'transaction_type' => Transaction::TYPE_RENEWAL,
                'subscription_type' => $data['subscription_type'],
                'amount' => $data['amount'],
                'status' => Transaction::STATUS_PENDING,
                'billing_period_start' => $nextDueAt,
                'billing_period_end' => $nextPeriodEnd,
                'due_at' => $nextDueAt,
                'paid_at' => null,
                'notes' => null,
            ]);

            AuditLogger::log(
                action: 'company.created',
                module: 'companies',
                description: "Company \"{$company->name}\" created.",
                company: $company,
                new: $company->only([
                    'name', 'business_name', 'email', 'phone', 'address',
                    'city', 'province', 'country', 'ntn_cnic', 'business_registration_number', 'status',
                ]),
            );

            return $company->refresh()->load(['users', 'subscriptions', 'transactions']);
        });
    }
}
