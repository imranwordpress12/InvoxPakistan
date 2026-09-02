<?php

namespace App\Domain\Transactions;

use App\Models\Transaction;
use Illuminate\Support\Str;

/**
 * Generates invoice numbers for new transactions. The random suffix makes a
 * collision astronomically unlikely, but this still checks against the
 * unique `transactions.invoice_number` index rather than assuming it away.
 */
class InvoiceNumberGenerator
{
    public static function generate(): string
    {
        do {
            $candidate = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Transaction::where('invoice_number', $candidate)->exists());

        return $candidate;
    }
}
