<?php

namespace Tests\Unit\Domain\Invoices\BulkUpload;

use App\Domain\Invoices\BulkUpload\BulkInvoiceCsvParser;
use App\Domain\Invoices\BulkUpload\InvalidBulkUploadFileException;
use PHPUnit\Framework\TestCase;

class BulkInvoiceCsvParserTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    private function fileWithContent(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'bulk_csv_test_');
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;

        return $path;
    }

    public function test_it_parses_a_valid_file_into_rows_keyed_by_data_row_number(): void
    {
        $header = implode(',', BulkInvoiceCsvParser::REQUIRED_COLUMNS);
        $row = implode(',', array_fill(0, count(BulkInvoiceCsvParser::REQUIRED_COLUMNS), 'x'));

        $rows = (new BulkInvoiceCsvParser)->parse($this->fileWithContent("{$header}\n{$row}\n"));

        $this->assertArrayHasKey(1, $rows);
        $this->assertSame('x', $rows[1]['invoice_reference_no']);
    }

    public function test_blank_cells_normalize_to_null_not_empty_string(): void
    {
        $header = implode(',', BulkInvoiceCsvParser::REQUIRED_COLUMNS);
        $row = implode(',', array_fill(0, count(BulkInvoiceCsvParser::REQUIRED_COLUMNS), ''));

        $rows = (new BulkInvoiceCsvParser)->parse($this->fileWithContent("{$header}\n{$row}\n"));

        $this->assertNull($rows[1]['sro_schedule_no']);
    }

    public function test_a_short_row_pads_missing_trailing_cells_to_null(): void
    {
        $header = implode(',', BulkInvoiceCsvParser::REQUIRED_COLUMNS);
        // Only the first three columns supplied.
        $row = 'INV-1,1234567-8,Acme';

        $rows = (new BulkInvoiceCsvParser)->parse($this->fileWithContent("{$header}\n{$row}\n"));

        $this->assertSame('INV-1', $rows[1]['invoice_reference_no']);
        $this->assertNull($rows[1]['sro_item_sr_no']);
    }

    public function test_it_rejects_an_empty_file(): void
    {
        $this->expectException(InvalidBulkUploadFileException::class);

        (new BulkInvoiceCsvParser)->parse($this->fileWithContent(''));
    }

    public function test_it_rejects_a_file_missing_required_columns(): void
    {
        $this->expectException(InvalidBulkUploadFileException::class);

        (new BulkInvoiceCsvParser)->parse($this->fileWithContent("invoice_reference_no,buyer_ntn_cnic\nINV-1,123\n"));
    }

    public function test_it_rejects_a_header_only_file_with_no_data_rows(): void
    {
        $header = implode(',', BulkInvoiceCsvParser::REQUIRED_COLUMNS);

        $this->expectException(InvalidBulkUploadFileException::class);

        (new BulkInvoiceCsvParser)->parse($this->fileWithContent("{$header}\n"));
    }

    public function test_a_leading_byte_order_mark_on_the_header_is_stripped(): void
    {
        $header = "\u{FEFF}".implode(',', BulkInvoiceCsvParser::REQUIRED_COLUMNS);
        $row = implode(',', array_fill(0, count(BulkInvoiceCsvParser::REQUIRED_COLUMNS), 'x'));

        $rows = (new BulkInvoiceCsvParser)->parse($this->fileWithContent("{$header}\n{$row}\n"));

        $this->assertArrayHasKey(1, $rows);
    }

    public function test_a_file_with_more_than_max_rows_data_rows_is_rejected(): void
    {
        $header = implode(',', BulkInvoiceCsvParser::REQUIRED_COLUMNS);
        $row = implode(',', array_fill(0, count(BulkInvoiceCsvParser::REQUIRED_COLUMNS), 'x'));
        $body = $header."\n".str_repeat("{$row}\n", BulkInvoiceCsvParser::MAX_ROWS + 1);

        $this->expectException(InvalidBulkUploadFileException::class);

        (new BulkInvoiceCsvParser)->parse($this->fileWithContent($body));
    }

    public function test_a_file_with_exactly_max_rows_data_rows_is_accepted(): void
    {
        $header = implode(',', BulkInvoiceCsvParser::REQUIRED_COLUMNS);
        $row = implode(',', array_fill(0, count(BulkInvoiceCsvParser::REQUIRED_COLUMNS), 'x'));
        $body = $header."\n".str_repeat("{$row}\n", BulkInvoiceCsvParser::MAX_ROWS);

        $rows = (new BulkInvoiceCsvParser)->parse($this->fileWithContent($body));

        $this->assertCount(BulkInvoiceCsvParser::MAX_ROWS, $rows);
    }
}
