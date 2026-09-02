<?php

namespace App\Http\Controllers\Company;

use App\Domain\Invoices\BulkUpload\BulkInvoiceCsvParser;
use App\Domain\Invoices\BulkUpload\ImportBulkInvoices;
use App\Domain\Invoices\BulkUpload\InvalidBulkUploadFileException;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Company Dashboard PRD #10 (Bulk Invoicing). Kept separate from
 * InvoiceController — this is a distinct workflow (a whole-file import,
 * not one form submission) with its own failure modes, same reasoning as
 * SubmitInvoiceToFbr living apart from the regular CRUD actions.
 */
class BulkInvoiceUploadController extends Controller
{
    public function create(): View
    {
        $this->authorize('create', Invoice::class);

        return view('company.invoices.bulk-upload', ['maxRows' => BulkInvoiceCsvParser::MAX_ROWS]);
    }

    public function store(Request $request, BulkInvoiceCsvParser $parser, ImportBulkInvoices $importer): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        try {
            $rows = $parser->parse($request->file('file')->getRealPath());
        } catch (InvalidBulkUploadFileException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        $result = $importer->handle($rows, $request->user()->company_id);

        return redirect()
            ->route('company.invoices.drafts')
            ->with('bulk_upload_created', $result->createdCount())
            ->with('bulk_upload_errors', $result->errors);
    }

    /**
     * A downloadable starting point matching {@see BulkInvoiceCsvParser::REQUIRED_COLUMNS}
     * exactly, with one filled-in example row — no stored file, generated
     * fresh on every request.
     */
    public function template(): Response
    {
        $this->authorize('create', Invoice::class);

        $columns = BulkInvoiceCsvParser::REQUIRED_COLUMNS;

        $example = [
            'INV-BULK-0001', '1234567-8', 'Acme Traders', '123 Main Street',
            'registered', 'SINDH', '12-34-5678-901-23', now()->toDateString(), 'sale',
            'Goods at standard rate (default)', '8415.8190', 'Laptop', '18', 'Number',
            '100', '2', '200', '0', '0', '0', '0', '0', '0', '', '',
        ];

        $csv = implode(',', $columns)."\r\n".implode(',', $example)."\r\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bulk-invoice-template.csv"',
        ]);
    }
}
