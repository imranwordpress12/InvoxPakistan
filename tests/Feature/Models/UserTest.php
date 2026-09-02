<?php

namespace Tests\Feature\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_has_no_company(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertNull($admin->company_id);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isCompany());
    }

    public function test_company_user_belongs_to_its_company(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company($company)->create();

        $this->assertTrue($user->company->is($company));
        $this->assertTrue($user->isCompany());
        $this->assertFalse($user->isAdmin());
    }

    public function test_password_and_remember_token_are_hidden_from_serialization(): void
    {
        $user = User::factory()->admin()->create();

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }
}
