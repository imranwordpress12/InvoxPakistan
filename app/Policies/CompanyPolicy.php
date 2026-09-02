<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

/**
 * Company management is admin-only. The `admin` route middleware already
 * keeps non-admins out of these controllers entirely, but every mutating
 * action is checked here too — authorization must not rely solely on route
 * placement or hidden UI (PRD #40).
 */
class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Company $company): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Company $company): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->isAdmin();
    }
}
