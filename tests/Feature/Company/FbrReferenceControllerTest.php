<?php

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class FbrReferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_rates_use_fbr_date_format_and_selected_live_type_and_province_codes(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://gw.fbr.gov.pk/pdi/v1/transtypecode' => Http::response([
                ['transactioN_TYPE_ID' => 75, 'transactioN_DESC' => ' Goods at standard rate (default) '],
            ]),
            'https://gw.fbr.gov.pk/pdi/v1/provinces' => Http::response([
                ['stateProvinceCode' => 7, 'stateProvinceDesc' => ' Punjab '],
            ]),
            'https://gw.fbr.gov.pk/pdi/v2/SaleTypeToRate*' => Http::response([
                ['ratE_ID' => 1, 'ratE_DESC' => 'Standard Rate', 'ratE_VALUE' => 18],
            ]),
        ]);

        [, $user] = $this->activeCompanyUser();

        $response = $this->actingAs($user)->getJson(route('company.invoices.reference.rates', [
            'sale_type' => 'Goods at standard rate (default)',
            'date' => '2026-01-15',
        ]));

        $response->assertOk()->assertJsonPath('rates.0.ratE_VALUE', 18);
        Http::assertSent(fn (Request $request): bool => str_starts_with(
            $request->url(),
            'https://gw.fbr.gov.pk/pdi/v2/SaleTypeToRate?'
        ) && $this->queryFrom($request) === [
            'date' => '15-Jan-2026',
            'transTypeId' => '75',
            'originationSupplier' => '7',
        ] && $request->hasHeader('Authorization', 'Bearer test-sandbox-token')
            && $request->hasHeader('Accept', 'application/json'));
    }

    public function test_rates_recover_after_a_temporary_fbr_server_error(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://gw.fbr.gov.pk/pdi/v1/transtypecode' => Http::response([
                ['transactioN_TYPE_ID' => 18, 'transactioN_DESC' => 'Services'],
            ]),
            'https://gw.fbr.gov.pk/pdi/v1/provinces' => Http::response([
                ['stateProvinceCode' => 8, 'stateProvinceDesc' => 'Sindh'],
            ]),
            'https://gw.fbr.gov.pk/pdi/v2/SaleTypeToRate*' => Http::sequence()
                ->push(['message' => 'temporarily unavailable'], 500)
                ->push([
                    ['ratE_ID' => 2, 'ratE_DESC' => 'Services Rate', 'ratE_VALUE' => 15],
                ]),
        ]);

        [, $user] = $this->activeCompanyUser('Sindh');

        $response = $this->actingAs($user)->getJson(route('company.invoices.reference.rates', [
            'sale_type' => 'Services',
            'date' => '2026-01-15',
        ]));

        $response->assertOk()->assertJsonPath('rates.0.ratE_VALUE', 15);
        Http::assertSentCount(4);
    }

    public function test_returns_502_and_logs_bounded_response_body_when_fbr_keeps_returning_500(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://gw.fbr.gov.pk/pdi/v1/transtypecode' => Http::response([
                ['transactioN_TYPE_ID' => 75, 'transactioN_DESC' => 'Goods at standard rate (default)'],
            ]),
            'https://gw.fbr.gov.pk/pdi/v1/provinces' => Http::response([
                ['stateProvinceCode' => 7, 'stateProvinceDesc' => 'Punjab'],
            ]),
            'https://gw.fbr.gov.pk/pdi/v2/SaleTypeToRate*' => Http::response(str_repeat('FBR exception details ', 150), 500),
        ]);
        Log::spy();

        [, $user] = $this->activeCompanyUser();

        $response = $this->actingAs($user)->getJson(route('company.invoices.reference.rates', [
            'sale_type' => 'Goods at standard rate (default)',
            'date' => '2026-01-15',
        ]));

        $response->assertStatus(502)->assertJsonPath(
            'message',
            'FBR returned an error (HTTP 500). Please retry.'
        );
        Log::shouldHaveReceived('error')
            ->once()
            ->with('FBR reference API returned an error status', Mockery::on(
                fn (array $context): bool => $context['status'] === 500
                    && strlen($context['response_body']) === 2000
                    && str_contains($context['response_body'], 'FBR exception details')
            ));
    }

    /**
     * @return array{Company, User}
     */
    private function activeCompanyUser(string $province = 'Punjab'): array
    {
        $company = Company::factory()->create([
            'province' => $province,
            'fbr_token_sandbox' => 'test-sandbox-token',
        ]);
        Subscription::factory()->for($company)->create([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(30),
        ]);
        $user = User::factory()->company($company)->create();

        return [$company, $user];
    }

    /**
     * @return array<string, string>
     */
    private function queryFrom(Request $request): array
    {
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query;
    }
}
