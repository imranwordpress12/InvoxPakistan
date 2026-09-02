<?php

namespace App\Http\Controllers\Company;

use App\Domain\Invoices\Fbr\FbrReferenceApiException;
use App\Domain\Invoices\Fbr\FbrReferenceService;
use App\Domain\Invoices\Fbr\FbrScenarioResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON endpoints backing the Create/Edit Invoice form's cascading
 * dropdowns (Invox_Pakistan_FBR_Invoice_Dependency_Implementation.md,
 * Section 26 — "Laravel Endpoint Design"). The browser only ever talks
 * to these; these in turn call FbrReferenceService, which is the only
 * thing that ever holds/sends the company's FBR token (Section 13/27 —
 * the token never reaches this controller, let alone the browser).
 *
 * Every action is scoped to the authenticated company via
 * `$request->user()->company_id` — never a request-supplied company ID
 * (Section 27) — and every action turns FbrReferenceApiException into a
 * plain `{message}` JSON body with a non-2xx status the frontend can
 * show verbatim as a "retry" state (Section 16), rather than a raw
 * Laravel exception page.
 */
class FbrReferenceController extends Controller
{
    public function __construct(
        private FbrReferenceService $reference,
        private FbrScenarioResolver $scenarios,
    ) {}

    public function provinces(Request $request): JsonResponse
    {
        return $this->respond(fn () => $this->reference->provinces($request->user()->company_id));
    }

    public function documentTypes(Request $request): JsonResponse
    {
        return $this->respond(fn () => $this->reference->documentTypes($request->user()->company_id));
    }

    public function itemCodes(Request $request): JsonResponse
    {
        return $this->respond(fn () => $this->reference->itemCodes($request->user()->company_id));
    }

    public function transactionTypes(Request $request): JsonResponse
    {
        return $this->respond(fn () => $this->reference->transactionTypes($request->user()->company_id));
    }

    /**
     * HS Code -> allowed UOM (Section 6.2's dependency rules; hs_code is
     * required so this can never be called "before HS Code exists",
     * Section 24 Rule 1).
     */
    public function hsUom(Request $request): JsonResponse
    {
        $data = $request->validate(['hs_code' => ['required', 'string', 'max:50']]);

        return $this->respond(fn () => $this->reference->hsUom($request->user()->company_id, $data['hs_code']));
    }

    /**
     * Sale Type -> Rate (Section 6.2). `transTypeId`/`originationSupplier`
     * are resolved server-side (never trusted from the browser — Section
     * 24 Rule 6) from the given `sale_type` name and the company's own
     * province. When either can't be resolved, responds with
     * `{"resolvable": false, "message": ...}` (HTTP 200 — this is an
     * expected, not exceptional, outcome) so the frontend falls back to
     * manual Rate entry instead of treating it as an error.
     */
    public function rates(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sale_type' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ]);

        $companyId = $request->user()->company_id;
        $company = $request->user()->company;

        $transTypeId = $this->reference->resolveTransactionTypeId($companyId, $data['sale_type']);
        $originationSupplier = $this->reference->resolveProvinceCode($companyId, $company?->province);

        if ($transTypeId === null || $originationSupplier === null) {
            return response()->json([
                'resolvable' => false,
                'message' => 'A live FBR rate lookup is not available for this Sale Type or seller province; enter the Rate manually.',
            ]);
        }

        return $this->respond(fn () => [
            'resolvable' => true,
            'rates' => $this->reference->saleTypeToRate($companyId, $transTypeId, $originationSupplier, $data['date']),
        ]);
    }

    /**
     * Rate -> applicable SRO/Schedule (Section 6.2). `rate_id` must be
     * the FBR `ratE_ID` returned by rates() above, not the plain
     * percentage — so SRO can only ever be requested once a *live* Rate
     * has actually been selected (Section 24 Rule 2's spirit, applied one
     * dependency level up).
     */
    public function sro(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rate_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            // Documented only by FBR's own sample value (Section 5.7) —
            // never guessed if the frontend can supply it, defaulted to
            // that same sample value otherwise. See
            // FbrReferenceService::sroSchedule()'s docblock.
            'origination_supplier_csv' => ['nullable', 'integer'],
        ]);

        return $this->respond(fn () => $this->reference->sroSchedule(
            $request->user()->company_id,
            $data['rate_id'],
            $data['date'],
            $data['origination_supplier_csv'] ?? 1,
        ));
    }

    /**
     * SRO -> its Item serial numbers (Section 6.2).
     */
    public function sroItems(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sro_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
        ]);

        return $this->respond(fn () => $this->reference->sroItems($request->user()->company_id, $data['sro_id'], $data['date']));
    }

    /**
     * Live scenario preview (Section 7/8 — "must happen before
     * submission"). Reuses FbrScenarioResolver directly rather than
     * re-implementing its rules in JavaScript (Section 31: don't
     * duplicate an existing implementation) so this can never drift from
     * what SandboxFbrInvoiceSubmitter actually enforces at submit time.
     */
    public function resolveScenario(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sale_types' => ['required', 'array', 'min:1'],
            'sale_types.*' => ['string'],
            'buyer_registration_type' => ['required', 'string'],
        ]);

        $resolved = collect($data['sale_types'])
            ->map(fn ($saleType) => $this->scenarios->resolve($saleType, $data['buyer_registration_type']))
            ->unique()
            ->values();

        $conflict = $resolved->filter()->count() > 1;

        return response()->json([
            'scenarioId' => $conflict ? null : $resolved->first(),
            'conflict' => $conflict,
        ]);
    }

    private function respond(\Closure $callback): JsonResponse
    {
        try {
            return response()->json($callback());
        } catch (FbrReferenceApiException $e) {
            return response()->json(['message' => $e->userMessage], 502);
        }
    }
}
