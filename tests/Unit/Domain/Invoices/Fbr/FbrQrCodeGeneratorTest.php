<?php

namespace Tests\Unit\Domain\Invoices\Fbr;

use App\Domain\Invoices\Fbr\FbrQrCodeGenerator;
use PHPUnit\Framework\TestCase;

class FbrQrCodeGeneratorTest extends TestCase
{
    public function test_it_generates_inline_svg_markup_with_no_xml_declaration(): void
    {
        $svg = (new FbrQrCodeGenerator)->generate('7000007DI1747119701593');

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringNotContainsString('<?xml', $svg);
    }

    public function test_different_values_produce_different_qr_codes(): void
    {
        $generator = new FbrQrCodeGenerator;

        $this->assertNotSame($generator->generate('INVOICE-A'), $generator->generate('INVOICE-B'));
    }
}
