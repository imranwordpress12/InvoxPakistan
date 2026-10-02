<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Transactions\Exceptions\TransactionAlreadyProcessedException;
use App\Domain\Transactions\MarkTransactionAsPaid;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MarkTransactionPaidRequest;
use App\Mail\PaymentThankYou;
use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);

        $transaction->load(['company', 'subscription']);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function screenshot(Transaction $transaction): BinaryFileResponse
    {
        $this->authorize('view', $transaction);

        abort_unless(
            $transaction->payment_screenshot
            && Storage::disk('local')->exists($transaction->payment_screenshot),
            404,
        );

        return response()->file(Storage::disk('local')->path($transaction->payment_screenshot));
    }

    /**
     * PRD #26/#53: mark a pending transaction paid and renew its
     * subscription, atomically. Redirects back to wherever the admin
     * triggered this from (company show page or the full transactions
     * listing) rather than a fixed destination.
     */
    public function markPaid(MarkTransactionPaidRequest $request, Transaction $transaction, MarkTransactionAsPaid $action): RedirectResponse
    {
        $data = $request->validated();
        $screenshotPath = $request->hasFile('payment_screenshot')
            ? $request->file('payment_screenshot')->store('payment-screenshots')
            : null;

        $amount = isset($data['amount']) && filled($data['amount']) ? (float) $data['amount'] : null;
        $subType = $data['subscription_type'] ?? null;
        $startsAt = isset($data['starts_at']) && filled($data['starts_at'])
            ? \Illuminate\Support\Carbon::parse($data['starts_at'])->startOfDay()
            : null;

        try {
            $paidTransaction = $action->handle(
                $transaction,
                $data['notes'] ?? null,
                $screenshotPath,
                $amount,
                $subType,
                $startsAt,
            );
        } catch (TransactionAlreadyProcessedException) {
            if ($screenshotPath) {
                Storage::delete($screenshotPath);
            }

            return back()->withErrors(['status' => 'This transaction has already been processed or is not eligible for payment.']);
        }

        return back()->with('status', "Transaction \"{$transaction->invoice_number}\" marked as paid.");
    }
}
