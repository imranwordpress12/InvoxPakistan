<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Transactions\Exceptions\TransactionAlreadyProcessedException;
use App\Domain\Transactions\MarkTransactionAsPaid;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MarkTransactionPaidRequest;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;

class TransactionController extends Controller
{
    /**
     * PRD #26/#53: mark a pending transaction paid and renew its
     * subscription, atomically. Redirects back to wherever the admin
     * triggered this from (company show page or the full transactions
     * listing) rather than a fixed destination.
     */
    public function markPaid(MarkTransactionPaidRequest $request, Transaction $transaction, MarkTransactionAsPaid $action): RedirectResponse
    {
        try {
            $action->handle($transaction, $request->string('notes')->value() ?: null);
        } catch (TransactionAlreadyProcessedException) {
            return back()->withErrors(['status' => 'This transaction has already been processed.']);
        }

        return back()->with('status', "Transaction \"{$transaction->invoice_number}\" marked as paid.");
    }
}
