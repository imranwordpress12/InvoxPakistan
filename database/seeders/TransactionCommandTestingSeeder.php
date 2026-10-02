<?php

namespace Database\Seeders;

use App\Domain\Subscriptions\SubscriptionPeriod;
use App\Domain\Transactions\InvoiceNumberGenerator;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionCommandTestingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {

            /*
            |--------------------------------------------------------------------------
            | COMPANY 1
            | End Date = Today + 7 Days
            | Status = Due
            | Test: transactions:send-payment-reminders
            |--------------------------------------------------------------------------
            */

            $this->createTestCompany(
                name: 'TEST - Reminder Company',
                email: 'imranwordpress12+1@gmail.com',
                endDate: now()->addDays(7),
                status: Transaction::STATUS_DUE,
                testNumber: 1
            );

            /*
            |--------------------------------------------------------------------------
            | COMPANY 2
            | End Date = Today
            | Status = Pending
            | Test: transactions:send-due-emails
            |--------------------------------------------------------------------------
            */

            $this->createTestCompany(
                name: 'TEST - Due Email Company',
                email: 'imranwordpress12+2@gmail.com',
                endDate: now(),
                status: Transaction::STATUS_PENDING,
                testNumber: 2
            );

            /*
            |--------------------------------------------------------------------------
            | COMPANY 3
            | End Date = Yesterday
            | Status = Pending
            | Test: subscriptions:process-expired
            |--------------------------------------------------------------------------
            */

            $this->createTestCompany(
                name: 'TEST - Expired Company',
                email: 'imranwordpress12+3@gmail.com',
                endDate: now()->subDay(),
                status: Transaction::STATUS_PENDING,
                testNumber: 3
            );
        });
    }

    private function createTestCompany(
        string $name,
        string $email,
        $endDate,
        string $status,
        int $testNumber
    ): void {
        $company = Company::create([
            'name' => $name,
            'business_name' => $name,
            'email' => $email,
            'phone' => '0300-0000000',
            'address' => 'Test Address',
            'city' => 'Lahore',
            'province' => 'Punjab',
            'country' => 'Pakistan',
            'ntn_cnic' => 'TEST-NTN-' . $testNumber,
            'business_registration_number' => 'TEST-REG-' . $testNumber,
            'status' => Company::STATUS_ACTIVE,
            'fbr_token_production' => 'test-production-token-' . $testNumber,
            'fbr_token_sandbox' => 'test-sandbox-token-' . $testNumber,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Company User
        |--------------------------------------------------------------------------
        */

        $company->users()->create([
            'name' => $name,
            'email' => $email,
            'password' => $email,
            'role' => User::ROLE_COMPANY,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Subscription
        |--------------------------------------------------------------------------
        */

        $subscriptionType = Subscription::TYPE_MONTHLY;
        $amount = 100.00;

        /*
         * We want the renewal transaction to have the exact
         * End Date required for each test case.
         *
         * Therefore calculate its Start Date as:
         * End Date - 1 month + 1 day
         */
        $billingPeriodEnd = $endDate->copy();
        $billingPeriodStart = $billingPeriodEnd->copy()
            ->subMonth()
            ->addDay();

        $subscription = $company->subscriptions()->create([
            'type' => $subscriptionType,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => $billingPeriodStart,
            'ends_at' => $billingPeriodEnd,
            'amount' => $amount,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Initial Paid Transaction
        |--------------------------------------------------------------------------
        */

        $company->transactions()->create([
            'subscription_id' => $subscription->id,
            'invoice_number' => InvoiceNumberGenerator::generate(),
            'transaction_type' => Transaction::TYPE_INITIAL,
            'subscription_type' => $subscriptionType,
            'amount' => $amount,
            'status' => Transaction::STATUS_PAID,
            'billing_period_start' => $billingPeriodStart,
            'billing_period_end' => $billingPeriodEnd,
            'due_at' => $billingPeriodStart,
            'paid_at' => $billingPeriodStart,
            'notes' => 'Test initial transaction',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Renewal Transaction
        |--------------------------------------------------------------------------
        */

        $company->transactions()->create([
            'subscription_id' => $subscription->id,
            'invoice_number' => InvoiceNumberGenerator::generate(),
            'transaction_type' => Transaction::TYPE_RENEWAL,
            'subscription_type' => $subscriptionType,
            'amount' => $amount,
            'status' => $status,
            'billing_period_start' => $billingPeriodStart,
            'billing_period_end' => $billingPeriodEnd,
            'due_at' => $billingPeriodEnd,
            'paid_at' => null,
            'notes' => 'Test transaction #' . $testNumber,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Buyer Customer
        |--------------------------------------------------------------------------
        */

        Customer::create([
            'company_id' => $company->id,
            'business_name' => 'TEST BUYER COMPANY ' . $testNumber,
            'ntn_cnic' => 'TEST-BUYER-' . $testNumber,
            'province' => 'PUNJAB',
            'buyer_registration_type' => 'registered',
            'strn' => null,
            'contact_person' => null,
            'email' => null,
            'contact_number' => null,
            'address' => 'Punjab, Pakistan',
            'status' => 'active',
        ]);
    }
}