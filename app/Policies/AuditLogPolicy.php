<?php

namespace App\Policies;

use App\Models\User;

/**
 * Audit logs are admin-only, no exceptions — a Company must never be able
 * to access them (PRD #4.2).
 */
class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
