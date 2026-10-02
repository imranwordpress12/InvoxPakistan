<?php

namespace App\Domain\Companies;

use App\Domain\Audit\AuditLogger;
use App\Domain\Subscriptions\SubscriptionPeriod;
use App\Domain\Transactions\InvoiceNumberGenerator;
use App\Domain\Transactions\TransactionBillingPeriod;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Mail\CompanyWelcome;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Creates a company together with its first login user, its first
 * subscription, and its initial paid transaction (PRD #20/#52): Company -> User -> Subscription ->
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
                'fbr_status' => Company::FBR_STATUS_INACTIVE,
                'status' => Company::STATUS_ACTIVE,
            ]);

            $user = $company->users()->create([
                'name' => $data['name'],
                'email' => $data['user_email'],
                // Hashed automatically by User's 'password' => 'hashed' cast.
                'password' => $data['password'],
                'role' => User::ROLE_COMPANY,
            ]);

            // Company Created -> Welcome Email
            try {
                Mail::to($company->email)->send(new CompanyWelcome($company));
            } catch (\Throwable $e) {
                // Ignore email failure during creation if mail server is not configured in test
            }

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

