<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

/**
 * Company Dashboard PRD #18: a company user may only ever touch its own
 * company's customers — never another company's, even by guessing an ID in
 * the URL. This is checked here, not just by scoping index/list queries,
 * so a direct show/edit/delete request on someone else's customer is
 * rejected server-side regardless of how the ID was obtained.
 */
class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCompany();
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->isCompany() && $user->company_id === $customer->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isCompany();
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->isCompany() && $user->company_id === $customer->company_id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->isCompany() && $user->company_id === $customer->company_id;
    }
}
