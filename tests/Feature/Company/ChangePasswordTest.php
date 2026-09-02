<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_company_user_can_change_their_password(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $response = $this->actingAs($user)->put(route('company.settings.password.update'), [
            'current_password' => 'password',
            'new_password' => 'brand-new-password',
            'new_password_confirmation' => 'brand-new-password',
        ]);

        $response->assertRedirect(route('company.settings.password.edit'));
        $this->assertTrue(
            auth()->getProvider()->validateCredentials($user->refresh(), ['password' => 'brand-new-password'])
        );
    }

    public function test_the_current_password_must_be_correct(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)->put(route('company.settings.password.update'), [
            'current_password' => 'wrong-password',
            'new_password' => 'brand-new-password',
            'new_password_confirmation' => 'brand-new-password',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(
            auth()->getProvider()->validateCredentials($user->refresh(), ['password' => 'password'])
        );
    }

    public function test_the_new_password_confirmation_must_match(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)->put(route('company.settings.password.update'), [
            'current_password' => 'password',
            'new_password' => 'brand-new-password',
            'new_password_confirmation' => 'something-else',
        ])->assertSessionHasErrors('new_password');
    }

    /**
     * Password management must remain reachable even while the
     * subscription is blocked (PRD's own settings section isn't gated
     * behind payment status).
     */
    public function test_a_blocked_company_user_can_still_change_their_password(): void
    {
        $company = Company::factory()->create();
        Subscription::factory()->for($company)->pending()->create();
        $user = User::factory()->company($company)->create();

        $this->actingAs($user)
            ->get(route('company.settings.password.edit'))
            ->assertOk();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('company.settings.password.edit'))->assertRedirect(route('company.login'));
    }

    /**
     * Regression test: the route is registered as PUT, but an HTML form
     * can only literally submit GET/POST — the rendered form must spoof
     * the method with @method('PUT'), or a real browser submission never
     * reaches the route at all. A test that calls ->put() directly
     * (as every test above does) can't catch this, since it bypasses the
     * rendered form entirely — caught only by a real manual browser
     * submission, then locked in here.
     */
    public function test_the_rendered_form_spoofs_the_put_method(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $response = $this->actingAs($user)->get(route('company.settings.password.edit'));

        $response->assertOk();
        $response->assertSee('<input type="hidden" name="_method" value="PUT">', false);
    }
}
