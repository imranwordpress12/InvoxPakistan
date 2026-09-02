<?php

namespace Tests\Unit\Domain;

use App\Domain\Transactions\InvoiceNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_a_dated_invoice_number(): void
    {
        $number = InvoiceNumberGenerator::generate();

        $this->assertMatchesRegularExpression('/^INV-\d{8}-[A-Z0-9]{6}$/', $number);
    }

    public function test_repeated_calls_are_unique(): void
    {
        $numbers = array_map(fn () => InvoiceNumberGenerator::generate(), range(1, 20));

        $this->assertCount(20, array_unique($numbers));
    }
}
