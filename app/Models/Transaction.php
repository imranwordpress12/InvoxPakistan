<?php

namespace App\Models;

use App\Policies\TransactionPolicy;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UsePolicy(TransactionPolicy::class)]
#[Fillable([
    'company_id',
    'subscription_id',
    'invoice_number',
    'transaction_type',
    'subscription_type',
    'amount',
    'status',
    'billing_period_start',
    'billing_period_end',
    'due_at',
    'paid_at',
    'notes',
])]
// `pending_dedupe_key` is a database-generated column (see the
// create_transactions_table migration): it exists only to back a unique
// index that blocks a second pending transaction per subscription. It is
// never mass-assignable (it isn't in the list above) and must never be
// serialized — it's an implementation detail, not application data.
#[Hidden(['pending_dedupe_key'])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    public const TYPE_INITIAL = 'initial';

    public const TYPE_RENEWAL = 'renewal';

    public const STATUS_PENDING = 'pending';

    public const STATUS_UNPAID = self::STATUS_PENDING;

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'billing_period_start' => 'date',
            'billing_period_end' => 'date',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
