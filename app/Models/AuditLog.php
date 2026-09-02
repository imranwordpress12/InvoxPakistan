<?php

namespace App\Models;

use App\Policies\AuditLogPolicy;
use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Never write passwords or FBR credentials into old_values/new_values
 * (PRD #33/#54) — AuditLogger::redact() strips them as a safety net, but
 * every call site is expected to already omit them.
 */
#[UsePolicy(AuditLogPolicy::class)]
#[Fillable([
    'user_id',
    'company_id',
    'action',
    'module',
    'description',
    'ip_address',
    'user_agent',
    'old_values',
    'new_values',
])]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
