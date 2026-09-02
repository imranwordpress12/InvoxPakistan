<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

/**
 * Company Dashboard PRD #18 — same reasoning as CustomerPolicy.
 */
class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCompany();
    }

    public function view(User $user, Item $item): bool
    {
        return $user->isCompany() && $user->company_id === $item->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isCompany();
    }

    public function update(User $user, Item $item): bool
    {
        return $user->isCompany() && $user->company_id === $item->company_id;
    }

    public function delete(User $user, Item $item): bool
    {
        return $user->isCompany() && $user->company_id === $item->company_id;
    }
}
