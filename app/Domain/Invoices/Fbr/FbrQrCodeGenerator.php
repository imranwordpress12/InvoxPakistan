<?php

namespace App\Domain\Invoices\Fbr;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * API Doc.pdf, Section 6 requires a QR code printed on every submitted
 * invoice, but — checked directly against every sample response in
 * Sections 4.1.3-4.2.4 — no QR-specific payload (URL/string/token/value)
 * is ever returned by this API version. The only invoice-identifying
 * value FBR actually returns is `invoiceNumber` (Section 4.1.3), so that
 * is what gets encoded here — not an arbitrary locally-invented value,
 * but also not a confirmed "this is FBR's real QR content" answer either.
 * If FBR's actual DI QR spec later turns out to encode something else
 * (e.g. a verification URL built from the invoice number), only this one
 * class needs to change.
 *
 * SVG output specifically (not PNG via GD/Imagick) — bacon/bacon-qr-code's
 * SvgImageBackEnd needs neither extension, keeping this dependency-light
 * on a plain shared-hosting-style PHP setup.
 */
class FbrQrCodeGenerator
{
    /**
     * Roughly matches the doc's "1.0 x 1.0 Inch" / "Version 2.0 (25x25)"
     * guidance for print sizing — an SVG scales cleanly regardless, this
     * just sets its intrinsic viewBox size.
     */
    private const SIZE_PX = 200;

    public function generate(string $value): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(self::SIZE_PX),
            new SvgImageBackEnd
        );

        $svg = (new Writer($renderer))->writeString($value);

        // Strip the leading XML declaration bacon-qr-code prepends — this
        // is embedded inline via {!! !!} in a Blade page, where that
        // declaration would otherwise render as literal text above the
        // image. (Comment deliberately avoids writing the literal
        // characters "question-mark greater-than" together — PHP's
        // lexer treats that exact sequence as the real closing tag even
        // inside a // comment, which is the bug this fixes.)
        return trim(preg_replace('/^<\?xml[^>]*\?'.'>\s*/', '', $svg));
    }
}
