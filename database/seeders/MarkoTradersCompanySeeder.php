<?php

namespace Database\Seeders;

use App\Domain\Subscriptions\SubscriptionPeriod;
use App\Domain\Transactions\InvoiceNumberGenerator;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarkoTradersCompanySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $company = Company::create([
                'name' => 'M/S MARKO TRADERS',
                'business_name' => 'M/S MARKO TRADERS',
                'email' => 'marko.traders@example.com',
                'phone' => '0300-0000000',
                'address' => '1 Test Street',
                'city' => 'Lahore',
                'province' => 'Punjab',
                'country' => 'Pakistan',
                'ntn_cnic' => '4515520',
                'business_registration_number' => 'REG-0001',
                'status' => Company::STATUS_ACTIVE,
                'fbr_token_production' => '3d5efb11-41cb-3abd-aa91-ee947153720e',
                'fbr_token_sandbox' => '3d5efb11-41cb-3abd-aa91-ee947153720e',
            ]);

            $company->users()->create([
                'name' => 'M/S MARKO TRADERS',
                'email' => 'test@gmail.com',
                'password' => 'test@gmail.com',
                'role' => User::ROLE_COMPANY,
            ]);

            $subscriptionType = Subscription::TYPE_MONTHLY;
            $amount = 100.00;
            $createdAt = $company->created_at->copy();
            $nextDueAt = SubscriptionPeriod::endDateFor($subscriptionType, $createdAt);

            $subscription = $company->subscriptions()->create([
                'type' => $subscriptionType,
                'status' => Subscription::STATUS_ACTIVE,
                'starts_at' => $createdAt,
                'ends_at' => $nextDueAt,
                'amount' => $amount,
            ]);

            $company->transactions()->create([
                'subscription_id' => $subscription->id,
                'invoice_number' => InvoiceNumberGenerator::generate(),
                'transaction_type' => Transaction::TYPE_INITIAL,
                'subscription_type' => $subscriptionType,
                'amount' => $amount,
                'status' => Transaction::STATUS_PAID,
                'billing_period_start' => $createdAt,
                'billing_period_end' => $nextDueAt,
                'due_at' => $createdAt,
                'paid_at' => $createdAt,
                'notes' => null,
            ]);

            $company->transactions()->create([
                'subscription_id' => $subscription->id,
                'invoice_number' => InvoiceNumberGenerator::generate(),
                'transaction_type' => Transaction::TYPE_RENEWAL,
                'subscription_type' => $subscriptionType,
                'amount' => $amount,
                'status' => Transaction::STATUS_PENDING,
                'billing_period_start' => $createdAt,
                'billing_period_end' => $nextDueAt,
                'due_at' => $nextDueAt,
                'paid_at' => null,
                'notes' => null,
            ]);
        });
    }
}