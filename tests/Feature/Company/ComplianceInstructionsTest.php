<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceInstructionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_company_user_can_view_it(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $response = $this->actingAs($user)->get(route('company.compliance-instructions'));

        $response->assertOk();
        $response->assertSee('Important Disclaimer');
        $response->assertSee('Invox Pakistan');
    }

    /**
     * Reachable even while blocked — a company should be able to read
     * the compliance rules regardless of subscription/payment status.
     */
    public function test_a_blocked_company_user_can_still_view_it(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->pending()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.compliance-instructions'))
            ->assertOk();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('company.compliance-instructions'))->assertRedirect(route('company.login'));
    }
}
