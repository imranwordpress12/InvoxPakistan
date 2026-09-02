<?php

namespace Tests\Unit\Policies;

use App\Models\Company;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_are_authorized(): void
    {
        $policy = new TransactionPolicy;
        $admin = User::factory()->admin()->create();
        $company = Company::factory()->create();
        $companyUser = User::factory()->company($company)->create();
        $transaction = Transaction::factory()->create(['company_id' => $company->id]);

        $this->assertTrue($policy->view($admin, $transaction));
        $this->assertTrue($policy->markAsPaid($admin, $transaction));

        $this->assertFalse($policy->view($companyUser, $transaction));
        $this->assertFalse($policy->markAsPaid($companyUser, $transaction));
    }
}
