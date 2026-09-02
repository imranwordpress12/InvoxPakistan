<?php

namespace App\Domain\Invoices\Fbr;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * FBR's Digital Invoicing *Reference* APIs (API Doc.pdf, Section 5),
 * cached per Section 15's rule: independent lists (provinces, document
 * types, transaction types, the full UOM list, HS codes) use one fixed
 * cache key each; context-dependent lists (HS_UOM, SaleTypeToRate, SRO,
 * SRO Item) key their cache entry on every parameter that affects the
 * response, never just the endpoint name — e.g. `hs_uom:{hsCode}` not
 * `hs_uom`, per Section 15's explicit example.
 *
 * Field names below are transcribed verbatim from FBR's own sample JSON
 * (including its inconsistent casing, e.g. `hS_CODE`, `srO_ID`,
 * `transactioN_TYPE_ID`) — not renamed/normalized, so a caller can
 * cross-check this code against the document line-for-line.
 */
class FbrReferenceService
{
    public function __construct(private FbrHttpClient $client) {}

    /**
     * @return array<int, array{stateProvinceCode: int, stateProvinceDesc: string}>
     */
    public function provinces(int $companyId): array
    {
        return $this->rememberIndependent($companyId, 'provinces', fn () => $this->client->get($companyId, 'v1/provinces'));
    }

    /**
     * @return array<int, array{docTypeId: int, docDescription: string}>
     */
    public function documentTypes(int $companyId): array
    {
        return $this->rememberIndependent($companyId, 'doctypecode', fn () => $this->client->get($companyId, 'v1/doctypecode'));
    }

    /**
     * @return array<int, array{hS_CODE: string, description: string}>
     */
    public function itemCodes(int $companyId): array
    {
        return $this->rememberIndependent($companyId, 'itemdesccode', fn () => $this->client->get($companyId, 'v1/itemdesccode'));
    }

    /**
     * @return array<int, array{transactioN_TYPE_ID: int, transactioN_DESC: string}>
     */
    public function transactionTypes(int $companyId): array
    {
        return $this->rememberIndependent($companyId, 'transtypecode', fn () => $this->client->get($companyId, 'v1/transtypecode'));
    }

    /**
     * @return array<int, array{uoM_ID: int, description: string}>
     */
    public function unitsOfMeasure(int $companyId): array
    {
        return $this->rememberIndependent($companyId, 'uom', fn () => $this->client->get($companyId, 'v1/uom'));
    }

    /**
     * HS Code -> allowed UOM (Section 5.9). `annexureId` defaults to the
     * project-wide assumption documented in config('services.fbr.hs_uom_annexure_id')
     * — see that config entry's comment for why this can't be derived
     * from the document itself.
     *
     * @return array<int, array{uoM_ID: int, description: string}>
     */
    public function hsUom(int $companyId, string $hsCode, ?int $annexureId = null): array
    {
        $annexureId ??= (int) config('services.fbr.hs_uom_annexure_id', 3);

        return $this->rememberDependent(
            $companyId,
            "hs_uom:{$hsCode}:{$annexureId}",
            fn () => $this->client->get($companyId, 'v2/HS_UOM', ['hs_code' => $hsCode, 'annexure_id' => $annexureId])
        );
    }

    /**
     * Transaction Type + origination province -> allowed Rate (Section
     * 5.8). `date` is required by FBR's own query string (Section 5.8.1)
     * — always pass the invoice date, not "today", since rates are
     * date-sensitive.
     *
     * @return array<int, array{ratE_ID: int, ratE_DESC: string, ratE_VALUE: float}>
     */
    public function saleTypeToRate(int $companyId, int $transTypeId, int $originationSupplier, string $date): array
    {
        return $this->rememberDependent(
            $companyId,
            "sale_type_to_rate:{$transTypeId}:{$originationSupplier}:{$date}",
            fn () => $this->client->get($companyId, 'v2/SaleTypeToRate', [
                'date' => $date,
                'transTypeId' => $transTypeId,
                'originationSupplier' => $originationSupplier,
            ])
        );
    }

    /**
     * Rate -> applicable SRO/Schedule (Section 5.7). `originationSupplierCsv`
     * is documented only by example (Section 5.7's sample URL passes `1`)
     * — forwarded through exactly as given, never defaulted/guessed here.
     *
     * @return array<int, array{srO_ID: int, srO_DESC: string}>
     */
    public function sroSchedule(int $companyId, int $rateId, string $date, int $originationSupplierCsv): array
    {
        return $this->rememberDependent(
            $companyId,
            "sro_schedule:{$rateId}:{$date}:{$originationSupplierCsv}",
            fn () => $this->client->get($companyId, 'v1/SroSchedule', [
                'rate_id' => $rateId,
                'date' => $date,
                'origination_supplier_csv' => $originationSupplierCsv,
            ])
        );
    }

    /**
     * SRO -> its Item serial numbers (Section 5.10).
     *
     * @return array<int, array{srO_ITEM_ID: int, srO_ITEM_DESC: string}>
     */
    public function sroItems(int $companyId, int $sroId, string $date): array
    {
        return $this->rememberDependent(
            $companyId,
            "sro_items:{$sroId}:{$date}",
            fn () => $this->client->get($companyId, 'v2/SROItem', ['date' => $date, 'sro_id' => $sroId])
        );
    }

    /**
     * Resolves a province name (as stored on Company/Invoice — free text
     * like "Sindh") to FBR's numeric `stateProvinceCode`, needed as the
     * `originationSupplier` parameter for saleTypeToRate(). Returns null
     * rather than guessing if no live province matches by name — callers
     * must treat that as "rate lookup unavailable", not fail loudly.
     */
    public function resolveProvinceCode(int $companyId, ?string $provinceName): ?int
    {
        if (blank($provinceName)) {
            return null;
        }

        foreach ($this->provinces($companyId) as $province) {
            if (Str::lower((string) ($province['stateProvinceDesc'] ?? '')) === Str::lower($provinceName)) {
                return (int) $province['stateProvinceCode'];
            }
        }

        return null;
    }

    /**
     * Resolves a local Sale Type name to FBR's numeric `transactioN_TYPE_ID`
     * (needed for saleTypeToRate()'s `transTypeId`). This project's Sale
     * Type list (see FbrScenarioResolver's docblock) is a fixed local
     * table chosen to match Section 9's scenario table, not sourced live
     * from `transtypecode` — that endpoint's real, full vocabulary isn't
     * confirmed to line up with it, so a match here is opportunistic:
     * found -> live Rate lookup proceeds; not found -> callers must fall
     * back to manual Rate entry rather than guessing an ID.
     */
    public function resolveTransactionTypeId(int $companyId, ?string $saleTypeName): ?int
    {
        if (blank($saleTypeName)) {
            return null;
        }

        foreach ($this->transactionTypes($companyId) as $type) {
            if (Str::lower((string) ($type['transactioN_DESC'] ?? '')) === Str::lower($saleTypeName)) {
                return (int) $type['transactioN_TYPE_ID'];
            }
        }

        return null;
    }

    /**
     * @return array<int|string, mixed>
     */
    private function rememberIndependent(int $companyId, string $key, \Closure $callback): array
    {
        return Cache::remember(
            "fbr_ref:{$companyId}:{$key}",
            (int) config('services.fbr.reference_cache_ttl', 43200),
            $callback
        );
    }

    /**
     * @return array<int|string, mixed>
     */
    private function rememberDependent(int $companyId, string $key, \Closure $callback): array
    {
        return Cache::remember(
            "fbr_ref:{$companyId}:{$key}",
            (int) config('services.fbr.dependent_reference_cache_ttl', 3600),
            $callback
        );
    }
}
