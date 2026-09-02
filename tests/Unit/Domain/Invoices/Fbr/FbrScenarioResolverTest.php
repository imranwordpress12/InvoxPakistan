<?php

namespace Tests\Unit\Domain\Invoices\Fbr;

use App\Domain\Invoices\Fbr\FbrScenarioResolver;
use App\Models\Customer;
use PHPUnit\Framework\TestCase;

/**
 * Every case here is a direct transcription of API Doc.pdf, Section 9
 * ("Scenarios for Sandbox Testing") — not a guess.
 */
class FbrScenarioResolverTest extends TestCase
{
    private FbrScenarioResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new FbrScenarioResolver;
    }

    public function test_standard_rate_resolves_differently_for_registered_vs_unregistered_buyers(): void
    {
        $this->assertSame(
            'SN001',
            $this->resolver->resolve('Goods at standard rate (default)', Customer::REGISTRATION_TYPE_REGISTERED)
        );
        $this->assertSame(
            'SN002',
            $this->resolver->resolve('Goods at standard rate (default)', Customer::REGISTRATION_TYPE_UNREGISTERED)
        );
    }

    public function test_it_resolves_sale_types_that_do_not_depend_on_buyer_registration(): void
    {
        $cases = [
            'Goods at Reduced Rate' => 'SN005',
            'Goods at zero-rate' => 'SN007',
            'Petroleum Products' => 'SN012',
            'Electricity Supply to Retailers' => 'SN013',
            'Gas to CNG stations' => 'SN014',
        ];

        foreach ($cases as $saleType => $expectedScenario) {
            $this->assertSame($expectedScenario, $this->resolver->resolve($saleType, Customer::REGISTRATION_TYPE_REGISTERED));
            $this->assertSame($expectedScenario, $this->resolver->resolve($saleType, Customer::REGISTRATION_TYPE_UNREGISTERED));
        }
    }

    /**
     * "SIM" has no row in Section 9's table — must stay unmapped rather
     * than guessed. "Processing/Conversion of Goods" etc. are similarly
     * left unmapped per the class's own docblock (ambiguous table
     * alignment around SN016-SN019).
     */
    public function test_an_unmapped_sale_type_resolves_to_null(): void
    {
        $this->assertNull($this->resolver->resolve('SIM', Customer::REGISTRATION_TYPE_REGISTERED));
        $this->assertNull($this->resolver->resolve('Not A Real Sale Type', Customer::REGISTRATION_TYPE_REGISTERED));
        $this->assertNull($this->resolver->resolve('Processing/Conversion of Goods', Customer::REGISTRATION_TYPE_REGISTERED));
    }

    /**
     * Confirmed live: FBR's own `transtypecode` response contains entries
     * with stray whitespace and inconsistent casing that a strict `===`
     * match would silently fail on, incorrectly blocking a submission
     * Section 9 actually supports.
     */
    public function test_matching_is_case_insensitive_and_trims_whitespace(): void
    {
        $this->assertSame('SN008', $this->resolver->resolve(' 3rd Schedule Goods ', Customer::REGISTRATION_TYPE_REGISTERED));
        $this->assertSame('SN019', $this->resolver->resolve(' Services ', Customer::REGISTRATION_TYPE_REGISTERED));
        $this->assertSame('SN006', $this->resolver->resolve('EXEMPT GOODS', Customer::REGISTRATION_TYPE_UNREGISTERED));
    }

    /**
     * Added once FBR's live `transtypecode` response (26 real Sale
     * Types) replaced this project's small hand-picked local list — Sale
     * Types that were previously never selectable (and so never needed
     * mapping) but are now genuinely reachable from the live dropdown.
     */
    public function test_it_resolves_sale_types_added_after_switching_to_the_live_transaction_type_list(): void
    {
        $cases = [
            'Cotton ginners' => 'SN009',
            'Toll Manufacturing' => 'SN011',
            'Electric Vehicle' => 'SN020',
            'Cement /Concrete Block' => 'SN021',
            'Potassium Chlorate' => 'SN022',
            'CNG Sales' => 'SN023',
            'Goods as per SRO.297(|)/2023' => 'SN024',
            'Non-Adjustable Supplies' => 'SN025',
            'Steel melting and re-rolling' => 'SN003',
            'Ship breaking' => 'SN004',
        ];

        foreach ($cases as $saleType => $expectedScenario) {
            $this->assertSame($expectedScenario, $this->resolver->resolve($saleType, Customer::REGISTRATION_TYPE_REGISTERED));
        }
    }
}
