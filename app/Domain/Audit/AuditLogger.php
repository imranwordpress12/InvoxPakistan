<?php

namespace App\Domain\Audit;

use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * The single place that writes AuditLog rows (PRD #33/#54): every caller
 * gets the same Who/When/IP handling for free, and a redaction safety net
 * means a stray password/FBR field can't reach the database even by
 * accident — see redact() below.
 */
class AuditLogger
{
    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    public static function log(
        string $action,
        string $module,
        ?string $description = null,
        ?Company $company = null,
        array $old = [],
        array $new = [],
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'company_id' => $company?->id,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'old_values' => self::redact($old) ?: null,
            'new_values' => self::redact($new) ?: null,
        ]);
    }

    /**
     * Strip sensitive keys before they can ever reach the database (PRD
     * #33/#54: "Sensitive credentials must never be stored inside
     * old_values/new_values"). Defense in depth — every call site is
     * expected to already omit these, but this is the one place that
     * guarantees it regardless.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function redact(array $values): array
    {
        return Arr::except($values, [
            'password',
            'password_confirmation',
            'fbr_token_production',
            'fbr_token_sandbox',
            'additional_credentials',
            'remember_token',
        ]);
    }
}
