<?php

namespace App\Domain\Invoices\Fbr;

use App\Models\Customer;
use Illuminate\Support\Str;

/**
 * Maps a Sale Type + buyer registration type to the FBR `scenarioId`
 * required on every Sandbox submission (API Doc.pdf, Section 9 — "Scenarios
 * for Sandbox Testing"; `scenarioId` itself is documented as "Required for
 * Sandbox only" in Section 4.1's field table).
 *
 * Deliberately a plain lookup table, not a guess: every entry here is a
 * direct transcription of Section 9's Sale-Type-to-Scenario column, cross-
 * checked against FBR's own live `transtypecode` response (confirmed live,
 * 26 real Sale Type values — the Create Invoice form's Sale Type dropdown
 * is now sourced directly from that same list, not a hand-picked local
 * subset). `resolve()` returns null for anything unmapped rather than
 * inventing a scenario ID, so an unmapped Sale Type fails submission with
 * a clear error instead of silently sending a wrong/guessed scenario.
 *
 * Matching is case-insensitive and trims whitespace on both sides —
 * confirmed necessary live: FBR's own `transtypecode` response contains
 * entries with stray leading/trailing spaces (`" 3rd Schedule Goods "`,
 * `" Services "`) and inconsistent casing (`"Exempt goods"` vs this
 * table's `"Exempt Goods"`) that a strict `===` comparison would silently
 * fail to match, incorrectly blocking a submission that Section 9 in fact
 * does support.
 *
 * Known gaps (flagged, not silently guessed) — Sale Types FBR's live
 * `transtypecode` list actually returns with no confident mapping here:
 * - `"SIM"` — no corresponding row in Section 9's table at all; the
 *   closest documented scenario is SN015 ("Sale of mobile phones" / Sale
 *   Type "Mobile Phones"), which is not the same thing as a SIM card.
 * - `"Processing/Conversion of Goods"`, `"Goods (FED in ST Mode)"`,
 *   `"Services (FED in ST Mode)"`, `"DTRE goods"` — Section 9's own table
 *   has its Sale-Type and Description columns visibly misaligned by the
 *   time it reaches plain-text extraction around SN016-SN019 (each row's
 *   Description text describes a different scenario than its own Sale
 *   Type cell); unlike the entries below, these could not be paired back
 *   to one specific SN code with confidence from the document alone, so
 *   they are left unmapped rather than guessed.
 * Any of these needs a real decision (get FBR's/PRAL's confirmation of
 * the exact SN code, or accept the submission will be blocked with a
 * clear error) — not a guess here.
 */
class FbrScenarioResolver
{
    /**
     * Sale Types whose scenario doesn't depend on buyer registration type.
     * Keys are normalized (trimmed, lowercased) before lookup — see
     * `normalize()` — so they're written here in the exact casing FBR's
     * live `transtypecode` response actually uses, for anyone
     * cross-checking this table against that response directly.
     *
     * @var array<string, string>
     */
    private const SCENARIOS = [
        'Goods at Reduced Rate' => 'SN005',
        'Exempt goods' => 'SN006',
        'Goods at zero-rate' => 'SN007',
        '3rd Schedule Goods' => 'SN008',
        'Cotton ginners' => 'SN009',
        'Telecommunication services' => 'SN010',
        'Toll Manufacturing' => 'SN011',
        'Petroleum Products' => 'SN012',
        'Electricity Supply to Retailers' => 'SN013',
        'Gas to CNG stations' => 'SN014',
        'Mobile Phones' => 'SN015',
        'Services' => 'SN019',
        'Electric Vehicle' => 'SN020',
        'Cement /Concrete Block' => 'SN021',
        'Potassium Chlorate' => 'SN022',
        'CNG Sales' => 'SN023',
        'Goods as per SRO.297(|)/2023' => 'SN024',
        'Non-Adjustable Supplies' => 'SN025',
        // Steel sector (SN003/SN004) — sale type text confirmed live,
        // scenario description transcribed from Section 9 directly.
        'Steel melting and re-rolling' => 'SN003',
        'Ship breaking' => 'SN004',
    ];

    /**
     * "Goods at standard rate (default)" is the one Sale Type whose
     * scenario genuinely depends on the buyer (Section 9: SN001 for
     * registered buyers, SN002 for unregistered).
     */
    private const STANDARD_RATE_SALE_TYPE = 'Goods at standard rate (default)';

    public function resolve(string $saleType, string $buyerRegistrationType): ?string
    {
        $normalized = self::normalize($saleType);

        if ($normalized === self::normalize(self::STANDARD_RATE_SALE_TYPE)) {
            return $buyerRegistrationType === Customer::REGISTRATION_TYPE_REGISTERED
                ? 'SN001'
                : 'SN002';
        }

        foreach (self::SCENARIOS as $knownSaleType => $scenarioId) {
            if ($normalized === self::normalize($knownSaleType)) {
                return $scenarioId;
            }
        }

        return null;
    }

    private static function normalize(string $value): string
    {
        return Str::lower(trim($value));
    }
}
