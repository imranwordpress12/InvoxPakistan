<?php

use App\Http\Controllers\Auth\CompanyLoginController;
use App\Http\Controllers\Company\BulkInvoiceUploadController;
use App\Http\Controllers\Company\ChangePasswordController;
use App\Http\Controllers\Company\CustomerController;
use App\Http\Controllers\Company\DashboardController;
use App\Http\Controllers\Company\FbrReferenceController;
use App\Http\Controllers\Company\InvoiceController;
use App\Http\Controllers\Company\ItemController;
use App\Http\Controllers\Company\SubscriptionRequiredController;
use Illuminate\Support\Facades\Route;

Route::prefix('company')->name('company.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [CompanyLoginController::class, 'create'])->name('login');
        Route::post('login', [CompanyLoginController::class, 'store']);
    });

    Route::middleware(['auth', 'company'])->group(function () {
        Route::post('logout', [CompanyLoginController::class, 'destroy'])->name('logout');

        // Reachable regardless of subscription state — this IS the page a
        // blocked company is redirected to, so it can't itself sit behind
        // the `subscription` middleware (that would be an infinite
        // redirect loop).
        Route::get('subscription-required', [SubscriptionRequiredController::class, 'index'])->name('subscription-required');

        // Also reachable regardless of subscription state (Company
        // Dashboard PRD): a blocked company should still be able to
        // change its password, and compliance information is safe to
        // read even while blocked.
        Route::get('settings/password', [ChangePasswordController::class, 'edit'])->name('settings.password.edit');
        Route::put('settings/password', [ChangePasswordController::class, 'update'])->name('settings.password.update');
        Route::view('compliance-instructions', 'company.compliance-instructions')->name('compliance-instructions');

        // The "main application" — everything a company only gets to use
        // while its subscription is active (PRD #10/#11).
        Route::middleware('subscription')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // "invoices/create", "invoices/drafts", "invoices/bulk-upload"
            // and "invoices/reference/*" must all be registered before
            // "invoices/{invoice}" or Laravel will try to resolve them as
            // an invoice route parameter.
            Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
            Route::get('invoices/drafts', [InvoiceController::class, 'drafts'])->name('invoices.drafts');
            Route::get('invoices/bulk-upload', [BulkInvoiceUploadController::class, 'create'])->name('invoices.bulk-upload.create');
            Route::post('invoices/bulk-upload', [BulkInvoiceUploadController::class, 'store'])->name('invoices.bulk-upload.store');
            Route::get('invoices/bulk-upload/template', [BulkInvoiceUploadController::class, 'template'])->name('invoices.bulk-upload.template');

            // FBR Invoice Form Dependency spec, Section 26 — JSON
            // endpoints for the Create/Edit form's cascading dropdowns.
            // The browser only ever talks to these; they in turn call
            // FbrReferenceService, the only thing holding the company's
            // FBR token.
            Route::prefix('invoices/reference')->name('invoices.reference.')->group(function () {
                Route::get('provinces', [FbrReferenceController::class, 'provinces'])->name('provinces');
                Route::get('document-types', [FbrReferenceController::class, 'documentTypes'])->name('document-types');
                Route::get('item-codes', [FbrReferenceController::class, 'itemCodes'])->name('item-codes');
                Route::get('transaction-types', [FbrReferenceController::class, 'transactionTypes'])->name('transaction-types');
                Route::get('hs-uom', [FbrReferenceController::class, 'hsUom'])->name('hs-uom');
                Route::get('rates', [FbrReferenceController::class, 'rates'])->name('rates');
                Route::get('sro', [FbrReferenceController::class, 'sro'])->name('sro');
                Route::get('sro-items', [FbrReferenceController::class, 'sroItems'])->name('sro-items');
                Route::post('resolve-scenario', [FbrReferenceController::class, 'resolveScenario'])->name('resolve-scenario');
            });

            Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
            Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
            Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
            Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
            Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
            Route::post('invoices/{invoice}/submit', [InvoiceController::class, 'submit'])->name('invoices.submit');

            // "customers/create" must be registered before "customers/{customer}".
            Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
            Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
            Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

            // "items/create" must be registered before "items/{item}".
            Route::get('items', [ItemController::class, 'index'])->name('items.index');
            Route::get('items/create', [ItemController::class, 'create'])->name('items.create');
            Route::post('items', [ItemController::class, 'store'])->name('items.store');
            Route::get('items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
            Route::put('items/{item}', [ItemController::class, 'update'])->name('items.update');
        });
    });
});
