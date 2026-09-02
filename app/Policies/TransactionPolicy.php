<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

/**
 * Transaction management is admin-only, same as CompanyPolicy. Whether a
 * transaction is currently *eligible* to be marked paid (i.e. still
 * pending) is a business-state check, not an authorization check — that
 * lives in MarkTransactionPaidRequest/MarkTransactionAsPaid instead.
 */
class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin();
    }

    public function markAsPaid(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin();
    }
}
