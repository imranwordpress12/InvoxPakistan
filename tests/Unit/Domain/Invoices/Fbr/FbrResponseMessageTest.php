<?php

namespace Tests\Unit\Domain\Invoices\Fbr;

use App\Domain\Invoices\Fbr\FbrResponseMessage;
use PHPUnit\Framework\TestCase;

class FbrResponseMessageTest extends TestCase
{
    public function test_it_extracts_the_top_level_error_from_real_fbr_json(): void
    {
        $json = json_encode([
            'dated' => '2025-05-13 13:09:05',
            'validationResponse' => [
                'statusCode' => '01',
                'status' => 'Invalid',
                'errorCode' => '0052',
                'error' => 'Provide proper HS Code with invoice no. null',
                'invoiceStatuses' => null,
            ],
        ]);

        $this->assertSame('Provide proper HS Code with invoice no. null', FbrResponseMessage::extract($json));
    }

    public function test_it_falls_back_to_the_first_item_level_error_when_no_top_level_error_exists(): void
    {
        $json = json_encode([
            'dated' => '2025-05-13 13:10:00',
            'validationResponse' => [
                'statusCode' => '00',
                'status' => 'invalid',
                'error' => '',
                'invoiceStatuses' => [
                    ['itemSNo' => '1', 'statusCode' => '01', 'status' => 'Invalid', 'invoiceNo' => null, 'errorCode' => '0046', 'error' => 'Provide rate.'],
                ],
            ],
        ]);

        $this->assertSame('Item 1: Provide rate.', FbrResponseMessage::extract($json));
    }

    public function test_a_plain_local_message_is_returned_verbatim(): void
    {
        $message = 'Configuration error: no FBR Sandbox token is set up for this company.';

        $this->assertSame($message, FbrResponseMessage::extract($message));
    }

    public function test_a_null_or_empty_response_gets_a_generic_fallback(): void
    {
        $this->assertNotEmpty(FbrResponseMessage::extract(null));
        $this->assertNotEmpty(FbrResponseMessage::extract(''));
    }
}
