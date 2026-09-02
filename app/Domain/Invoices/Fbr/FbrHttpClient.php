<?php

namespace App\Domain\Invoices\Fbr;

use App\Models\Company;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin, generic GET client for FBR's Digital Invoicing *Reference* APIs
 * (API Doc.pdf, Section 5 — `{reference_base_url}/v1/...` and `/v2/...`).
 * Every one of them is documented as Bearer-token authenticated the same
 * way as the submission endpoints (Section 3's "Bearer" note applies
 * project-wide, not just to Section 4) and reuses the exact
 * company-token lookup already established by SandboxFbrInvoiceSubmitter.
 *
 * Sandbox-only, matching this project's current scope: always reads
 * `fbr_token_sandbox`. Extending to Production later is the same shape
 * of change as it would be for SandboxFbrInvoiceSubmitter.
 *
 * Deliberately never puts the token into a log line, an exception
 * message, or anywhere a caller could accidentally surface it (Section
 * 13/22/27) — only Http::withToken() ever sees the raw value.
 */
class FbrHttpClient
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<int|string, mixed>
     */
    public function get(int $companyId, string $path, array $query = []): array
    {
        $token = Company::whereKey($companyId)->value('fbr_token_sandbox');

        if (blank($token)) {
            throw new FbrReferenceApiException(
                'Configuration error: no FBR Sandbox token is set up for this company. '.
                'Ask an administrator to add one under Company Settings.'
            );
        }

        $url = rtrim((string) config('services.fbr.reference_base_url'), '/').'/'.ltrim($path, '/');

        try {
            $response = Http::withToken($token)
                ->timeout((int) config('services.fbr.timeout', 30))
                ->acceptJson()
                ->get($url, $query);
        } catch (ConnectionException $e) {
            Log::error('FBR reference API unreachable', [
                'path' => $path,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            throw new FbrReferenceApiException(
                'Unable to reach FBR right now. Please retry in a moment.',
                $e
            );
        }

        if (! $response->successful()) {
            Log::error('FBR reference API returned an error status', [
                'path' => $path,
                'query' => $query,
                'status' => $response->status(),
            ]);

            throw new FbrReferenceApiException(
                $response->status() === 401
                    ? 'FBR rejected the request as unauthorized. Check the company\'s FBR Sandbox token.'
                    : 'FBR returned an error (HTTP '.$response->status().'). Please retry.'
            );
        }

        $body = $response->json();

        if (! is_array($body)) {
            Log::error('FBR reference API returned a non-JSON body', ['path' => $path, 'query' => $query]);

            throw new FbrReferenceApiException('FBR returned a response that could not be understood. Please retry.');
        }

        return $body;
    }
}
