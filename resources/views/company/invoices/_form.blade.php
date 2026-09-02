@php
    // $invoice is null on create. On a validation-failure redisplay,
    // old('items') must win over both — otherwise a rejected submission
    // would silently drop every item the user just entered.
    $existingItems = old('items')
        ? collect(old('items'))->values()
        : ($invoice ? $invoice->items->map(fn ($i) => [
            'item_id' => $i->item_id,
            'sale_type' => $i->sale_type,
            'hs_code' => $i->hs_code,
            'product_description' => $i->product_description,
            'rate' => (float) $i->rate,
            'uom' => $i->uom,
            'price_per_unit' => (float) $i->price_per_unit,
            'quantity' => (float) $i->quantity,
            'value_sales_excl_st' => (float) $i->value_sales_excl_st,
            'sales_tax' => (float) $i->sales_tax,
            'further_tax' => (float) $i->further_tax,
            'fixed_retail_price' => (float) $i->fixed_retail_price,
            'st_withheld_at_source' => (float) $i->st_withheld_at_source,
            'extra_tax' => (float) $i->extra_tax,
            'fed_payable' => (float) $i->fed_payable,
            'discount' => (float) $i->discount,
            'total_sales_value' => (float) $i->total_sales_value,
            'sro_schedule_no' => $i->sro_schedule_no,
            'sro_item_sr_no' => $i->sro_item_sr_no,
        ])->values() : collect());

    // Blade's @json() directive naively explodes its argument on every
    // comma (it only expects "value, options, depth") — any multi-key
    // array literal passed straight into @json() gets silently mangled.
    // Precompute into plain variables here instead.
    $itemsMasterData = $items->map(fn ($i) => [
        'id' => $i->id,
        'sale_type' => $i->sale_type,
        'hs_code' => $i->hs_code,
        'description' => $i->description ?: $i->item_name,
        'uom' => $i->uom,
        // Formatted to exactly match the Rate (%) <option value="...">
        // below (also a decimal:2 cast rendered raw) — the entry form's
        // JS sets the <select>'s .value from this string, and HTML option
        // matching is an exact string comparison: "18" would silently
        // fail to match an option written as "18.00".
        'rate' => number_format((float) $i->rate, 2, '.', ''),
        'price_per_unit' => (float) $i->sale_price,
    ]);

    $customersMasterData = $customers->map(fn ($c) => [
        'id' => $c->id,
        'ntn_cnic' => $c->ntn_cnic,
        'business_name' => $c->business_name,
        'address' => $c->address,
        'registration_type' => $c->buyer_registration_type,
        'province' => $c->province,
        'strn' => $c->strn,
    ]);
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form
    method="POST"
    action="{{ $invoice ? route('company.invoices.update', $invoice) : route('company.invoices.store') }}"
    id="invoice-form"
    novalidate
>
    @csrf
    @if ($invoice)
        @method('PUT')
    @endif
    <input type="hidden" name="action" id="invoice-action" value="draft">

    <div class="card shadow-sm mb-3">
        <div class="card-header">Invoice Details</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Customer (from master)</label>
                <select id="customer-select" class="form-select">
                    <option value="">Select Customer (optional)</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string) old('customer_id', $invoice?->customer_id) === (string) $customer->id)>
                            {{ $customer->business_name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id', $invoice?->customer_id) }}">
                <div class="form-text">Optional. Select to prefill buyer details from an existing customer.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">
                    Buyer NTN/CNIC
                    <i class="bi bi-info-circle" title="Enter 7-digit NTN and 13-digit CNIC without dashes"></i>
                    <span class="text-danger" id="buyer_ntn_cnic-required-mark" @style(['display:none' => (old('buyer_registration_type', $invoice?->buyer_registration_type) === 'unregistered')])>*</span>
                </label>
                <input
                    type="text"
                    name="buyer_ntn_cnic"
                    id="buyer_ntn_cnic"
                    value="{{ old('buyer_ntn_cnic', $invoice?->buyer_ntn_cnic) }}"
                    class="form-control @error('buyer_ntn_cnic') is-invalid @enderror"
                    @if (old('buyer_registration_type', $invoice?->buyer_registration_type) !== 'unregistered') required @endif
                >
                <div class="form-text">
                    7-digit NTN or 13-digit CNIC, no dashes.
                    <span class="text-muted">Optional for Unregistered buyers.</span>
                </div>
                @error('buyer_ntn_cnic') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Buyer Business Name <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="buyer_business_name"
                    id="buyer_business_name"
                    value="{{ old('buyer_business_name', $invoice?->buyer_business_name) }}"
                    class="form-control @error('buyer_business_name') is-invalid @enderror"
                    required
                >
                @error('buyer_business_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Buyer Address <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="buyer_address"
                    id="buyer_address"
                    value="{{ old('buyer_address', $invoice?->buyer_address) }}"
                    class="form-control @error('buyer_address') is-invalid @enderror"
                    required
                >
                @error('buyer_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-2">
                <label class="form-label">Buyer Registration Type <span class="text-danger">*</span></label>
                <select
                    name="buyer_registration_type"
                    id="buyer_registration_type"
                    class="form-select @error('buyer_registration_type') is-invalid @enderror"
                    required
                >
                    <option value="" disabled @selected(! old('buyer_registration_type', $invoice?->buyer_registration_type))>Select...</option>
                    <option value="registered" @selected(old('buyer_registration_type', $invoice?->buyer_registration_type) === 'registered')>Registered</option>
                    <option value="unregistered" @selected(old('buyer_registration_type', $invoice?->buyer_registration_type) === 'unregistered')>Unregistered</option>
                </select>
                @error('buyer_registration_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-2">
                <label class="form-label">
                    Buyer Province <span class="text-danger">*</span>
                    <i class="bi bi-info-circle" title="Loaded live from FBR's provinces reference API."></i>
                </label>
                <select
                    name="buyer_province"
                    id="buyer_province"
                    class="form-select @error('buyer_province') is-invalid @enderror"
                    required
                >
                    <option value="" disabled @selected(! old('buyer_province', $invoice?->buyer_province))>Select...</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province }}" @selected(old('buyer_province', $invoice?->buyer_province) === $province)>{{ $province }}</option>
                    @endforeach
                </select>
                <div class="form-text small" id="buyer_province-status"></div>
                @error('buyer_province') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-2">
                <label class="form-label">Buyer STRN</label>
                <input
                    type="text"
                    name="buyer_strn"
                    id="buyer_strn"
                    value="{{ old('buyer_strn', $invoice?->buyer_strn) }}"
                    class="form-control @error('buyer_strn') is-invalid @enderror"
                >
                @error('buyer_strn') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                <input
                    type="date"
                    name="invoice_date"
                    id="invoice_date"
                    value="{{ old('invoice_date', $invoice?->invoice_date?->toDateString() ?? now()->toDateString()) }}"
                    class="form-control @error('invoice_date') is-invalid @enderror"
                    required
                >
                @error('invoice_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">
                    Invoice Type <span class="text-danger">*</span>
                    <i class="bi bi-info-circle" title="Loaded live from FBR's doctypecode reference API."></i>
                </label>
                <select name="invoice_type" id="invoice_type" class="form-select @error('invoice_type') is-invalid @enderror" required>
                    <option value="sale" @selected(old('invoice_type', $invoice?->invoice_type ?? 'sale') === 'sale')>Sale Invoice</option>
                    <option value="debit" @selected(old('invoice_type', $invoice?->invoice_type) === 'debit')>Debit Note</option>
                    <option value="credit" @selected(old('invoice_type', $invoice?->invoice_type) === 'credit')>Credit Note</option>
                </select>
                <div class="form-text small" id="invoice_type-status"></div>
                @error('invoice_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Invoice Reference No</label>
                <input
                    type="text"
                    name="invoice_reference_no"
                    value="{{ old('invoice_reference_no', $invoice?->invoice_reference_no) }}"
                    class="form-control @error('invoice_reference_no') is-invalid @enderror"
                >
                @error('invoice_reference_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header">
            Item Details
            <i class="bi bi-info-circle" title="Add one or more items, then Save as Draft or Submit."></i>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Item (from master)</label>
                    <select id="item-select" class="form-select">
                        <option value="">Select Item (from master)</option>
                        @foreach ($items as $masterItem)
                            <option value="{{ $masterItem->id }}">{{ $masterItem->item_name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        Optional. Select to prefill description, HS code, UoM and price; quantity and FBR fields are still required.
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        Sale Type <span class="text-danger">*</span>
                        <i class="bi bi-info-circle" title="Loaded live from FBR's transtypecode reference API."></i>
                    </label>
                    <select id="entry-sale_type" class="form-select">
                        <option value="">Select Sale Type</option>
                        @foreach ($saleTypes as $saleType)
                            <option value="{{ $saleType }}">{{ $saleType }}</option>
                        @endforeach
                    </select>
                    <div class="form-text small" id="entry-sale_type-status"></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        HS Code <span class="text-danger">*</span>
                        <i class="bi bi-info-circle" title="Loaded live from FBR's itemdesccode reference API."></i>
                    </label>
                    <div class="position-relative">
                        <input
                            type="text"
                            id="entry-hs_code"
                            class="form-control"
                            placeholder="Type to search HS Code..."
                            autocomplete="off"
                        >
                        <div
                            id="entry-hs_code-suggestions"
                            class="list-group position-absolute w-100 shadow-sm"
                            style="z-index: 1000; max-height: 260px; overflow-y: auto; display: none;"
                        ></div>
                    </div>
                    <div class="form-text small" id="entry-hs_code-description"></div>
                    <div class="form-text small" id="entry-hs_code-status"></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Product Description <span class="text-danger">*</span></label>
                    <input type="text" id="entry-product_description" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Rate (%) <span class="text-danger">*</span>
                        <i class="bi bi-info-circle" title="Loaded live from FBR's SaleTypeToRate reference API once a Sale Type is selected."></i>
                    </label>
                    <select id="entry-rate" class="form-select">
                        <option value="">Select Rate (%)</option>
                        @foreach ($taxRates as $taxRate)
                            <option value="{{ $taxRate->rate }}">{{ number_format($taxRate->rate, 2) }}%</option>
                        @endforeach
                    </select>
                    <div class="form-text small" id="entry-rate-status"></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">UoM <span class="text-danger">*</span></label>
                    <select id="entry-uom" class="form-select" disabled>
                        <option value="">Select HS Code first</option>
                    </select>
                    <div class="form-text small" id="entry-uom-status"></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Price/Unit</label>
                    <input type="number" step="0.01" min="0" id="entry-price_per_unit" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" min="0.0001" id="entry-quantity" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Value of Sales Excl. ST
                        <i class="bi bi-info-circle" title="Auto-filled from Price/Unit x Quantity, or enter directly."></i>
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" id="entry-value_sales_excl_st" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sales Tax <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" id="entry-sales_tax" class="form-control" readonly value="0.00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">
                        Further Tax
                        <i class="bi bi-info-circle" title="0 by default; check the box below to enter a value manually."></i>
                    </label>
                    <input type="number" step="0.01" min="0" id="entry-further_tax" class="form-control" readonly value="0.00">
                    <div class="form-check mt-1">
                        <input type="checkbox" class="form-check-input" id="entry-manual-further-tax">
                        <label class="form-check-label small" for="entry-manual-further-tax">Set Manual FurtherTax</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fixed / notified value or Retail Price</label>
                    <input type="number" step="0.01" min="0" id="entry-fixed_retail_price" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">ST withheld at Source</label>
                    <input type="number" step="0.01" min="0" id="entry-st_withheld_at_source" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Extra Tax</label>
                    <input type="number" step="0.01" min="0" id="entry-extra_tax" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fed Payable</label>
                    <input type="number" step="0.01" min="0" id="entry-fed_payable" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Discount</label>
                    <input type="number" step="0.01" min="0" id="entry-discount" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Sales Value <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" id="entry-total_sales_value" class="form-control" readonly value="0.00">
                </div>
                <div class="col-md-4">
                    <label class="form-label">
                        SRO / Schedule No
                        <i class="bi bi-info-circle" title="Becomes a live FBR dropdown once a live Rate is selected; otherwise optional free text."></i>
                    </label>
                    <div id="entry-sro_schedule_no-wrap">
                        <input type="text" id="entry-sro_schedule_no" class="form-control">
                    </div>
                    <div class="form-text small" id="entry-sro_schedule_no-status"></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">SRO Item Sr. No</label>
                    <div id="entry-sro_item_sr_no-wrap">
                        <input type="text" id="entry-sro_item_sr_no" class="form-control">
                    </div>
                    <div class="form-text small" id="entry-sro_item_sr_no-status"></div>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="button" id="add-item-btn" class="btn btn-accent-coral">
                    <i class="bi bi-plus-lg me-1"></i> Add Item
                </button>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-sm align-middle" id="items-table">
                    <thead>
                        <tr>
                            <th>Sale Type</th>
                            <th>HS Code</th>
                            <th>Product Desc.</th>
                            <th>Rate (%)</th>
                            <th>UoM</th>
                            <th>Qty</th>
                            <th>Price/Unit</th>
                            <th>Value of Sales Excl. ST</th>
                            <th>Sales Tax</th>
                            <th>Total Sales Value</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody"></tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="7">Total</td>
                            <td id="totals-excl-st">0.00</td>
                            <td id="totals-sales-tax">0.00</td>
                            <td id="totals-total">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div id="items-hidden-inputs"></div>
            <div id="scenario-preview" class="alert small mt-3" style="display:none;"></div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="button" id="preview-btn" class="btn btn-soft-mint">Preview Invoice</button>
        <button type="submit" id="save-draft-btn" class="btn btn-soft-rose">Save as Draft</button>
        <button type="submit" id="submit-invoice-btn" class="btn btn-primary">Submit Invoice</button>
        <a href="{{ route('company.invoices.drafts') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<div class="modal fade" id="preview-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invoice Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="preview-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);

    let items = @json($existingItems);

    const itemsMaster = @json($itemsMasterData);

    const customersMaster = @json($customersMasterData);

    // Full offline lists — the always-available last-resort fallback
    // whenever a live FBR reference lookup below can't be used (Section
    // 16: network errors must degrade gracefully, never hard-block
    // draft/invoice entry just because FBR itself is unreachable or
    // unconfigured for this company). Every one of these is a local
    // reference table this project already had *before* this live
    // integration — never the primary source, only the safety net.
    // Captured once, before any dynamic swap, so each can always be
    // restored exactly.
    const staticUomOptions = @json($unitsOfMeasure);
    const staticRateOptionsHtml = $('entry-rate').innerHTML;
    const staticHsCodes = @json($hsCodes->map(fn ($h) => ['code' => $h->code, 'description' => $h->description]));
    const staticProvinceOptionsHtml = $('buyer_province').innerHTML;
    const staticInvoiceTypeOptionsHtml = $('invoice_type').innerHTML;
    const staticSaleTypeOptionsHtml = $('entry-sale_type').innerHTML;

    const referenceUrls = {
        provinces: @json(route('company.invoices.reference.provinces')),
        documentTypes: @json(route('company.invoices.reference.document-types')),
        transactionTypes: @json(route('company.invoices.reference.transaction-types')),
        itemCodes: @json(route('company.invoices.reference.item-codes')),
        hsUom: @json(route('company.invoices.reference.hs-uom')),
        rates: @json(route('company.invoices.reference.rates')),
        sro: @json(route('company.invoices.reference.sro')),
        sroItems: @json(route('company.invoices.reference.sro-items')),
        resolveScenario: @json(route('company.invoices.reference.resolve-scenario')),
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function num(id) {
        return parseFloat($(id).value) || 0;
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    // ---------------------------------------------------------------
    // Shared helpers for every FBR reference-API call below (loading /
    // empty / error / retry states — Section 16).
    // ---------------------------------------------------------------

    function setStatus(id, message, cssClass) {
        const el = $(id);
        if (!el) return;
        el.textContent = message || '';
        el.className = 'form-text small' + (cssClass ? ' ' + cssClass : '');
    }

    function addRetryLink(statusId, retryFn) {
        const el = $(statusId);
        if (!el) return;
        el.appendChild(document.createTextNode(' '));
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-link btn-sm p-0 align-baseline';
        btn.textContent = 'Retry';
        btn.addEventListener('click', retryFn);
        el.appendChild(btn);
    }

    function fetchReference(url, params) {
        const qs = params ? ('?' + new URLSearchParams(params).toString()) : '';
        return fetch(url + qs, { headers: { Accept: 'application/json' } })
            .then((res) => res.json().catch(() => ({})).then((body) => ({ ok: res.ok, body })));
    }

    /**
     * Populates a plain <select> from a live list, preserving whatever
     * value was already selected (old()/invoice redisplay) if it's still
     * among the new options. Falls back to the given static HTML
     * snapshot on failure/empty response — used identically for
     * Province, Invoice Type, and Sale Type below.
     */
    function populateIndependentSelect(selectId, statusId, url, mapRow, staticHtml, successNoun) {
        const select = $(selectId);
        const previousValue = select.value;

        setStatus(statusId, `Loading ${successNoun} from FBR...`);

        return fetchReference(url).then(({ ok, body }) => {
            const rows = ok && Array.isArray(body) ? body.map(mapRow).filter((row) => row && row.value) : [];

            if (rows.length === 0) {
                select.innerHTML = staticHtml;
                select.value = previousValue;
                setStatus(statusId, ((! ok && body && body.message) || `FBR returned no ${successNoun}.`) + ' Showing the offline list instead.', 'text-warning');
                addRetryLink(statusId, () => populateIndependentSelect(selectId, statusId, url, mapRow, staticHtml, successNoun));
                return;
            }

            const placeholder = selectId === 'buyer_province' ? '<option value="" disabled>Select...</option>' : '';
            select.innerHTML = placeholder + rows.map((row) => (
                `<option value="${escapeHtml(row.value)}">${escapeHtml(row.label)}</option>`
            )).join('');

            if (rows.some((row) => row.value === previousValue)) {
                select.value = previousValue;
            }

            setStatus(statusId, `${rows.length} ${successNoun} loaded live from FBR.`, 'text-success');
        }).catch(() => {
            select.innerHTML = staticHtml;
            select.value = previousValue;
            setStatus(statusId, `Unable to reach FBR. Showing the offline ${successNoun} list instead.`, 'text-warning');
            addRetryLink(statusId, () => populateIndependentSelect(selectId, statusId, url, mapRow, staticHtml, successNoun));
        });
    }

    // ---- Province (Section 5.1: `provinces`) — independent ----

    function loadProvinces() {
        return populateIndependentSelect(
            'buyer_province', 'buyer_province-status', referenceUrls.provinces,
            (row) => ({ value: row.stateProvinceDesc, label: row.stateProvinceDesc }),
            staticProvinceOptionsHtml, 'Provinces'
        );
    }

    // ---- Invoice Type (Section 5.2: `doctypecode`) — independent ----
    // FBR's docDescription values are mapped onto this app's own
    // internal invoice_type enum ("sale"/"debit"/"credit" — driving
    // Invoice::TYPE_* logic elsewhere in this app) so submitting the
    // form keeps working unchanged; the live docDescription text is
    // still what's actually shown to the user, never a hard-coded label.
    // A docType FBR returns that doesn't map to a known internal type is
    // skipped, not fabricated.
    const INVOICE_TYPE_MAP = { 'sale invoice': 'sale', 'debit note': 'debit', 'credit note': 'credit' };

    function loadInvoiceTypes() {
        return populateIndependentSelect(
            'invoice_type', 'invoice_type-status', referenceUrls.documentTypes,
            (row) => {
                const internal = INVOICE_TYPE_MAP[(row.docDescription || '').toLowerCase()];
                return internal ? { value: internal, label: row.docDescription } : null;
            },
            staticInvoiceTypeOptionsHtml, 'Invoice Types'
        );
    }

    // ---- Sale Type (Section 5.5: `transtypecode`) — independent ----
    // The value stored/submitted is FBR's own transactioN_DESC text
    // verbatim (this is also exactly what FbrScenarioResolver and the
    // `saleType` FBR payload field expect) — never a locally-invented
    // string.

    function loadSaleTypesList() {
        return populateIndependentSelect(
            'entry-sale_type', 'entry-sale_type-status', referenceUrls.transactionTypes,
            (row) => (row.transactioN_DESC ? { value: row.transactioN_DESC, label: row.transactioN_DESC } : null),
            staticSaleTypeOptionsHtml, 'Sale Types'
        );
    }

    // ---------------------------------------------------------------
    // FBR reference-API dependency chains (FBR Invoice Form Dependency
    // spec, Sections 4-6, 16, 17, 24, 28). Every dependent field is
    // disabled/reset until its parent is chosen, every fetch is guarded
    // against being overtaken by a newer one for the same field
    // (Section 16's "parent change during request" rule), and every
    // failure/empty-result path degrades to the offline static list
    // rather than leaving the form stuck (this app already treats FBR
    // unavailability as recoverable everywhere else — see
    // SandboxFbrInvoiceSubmitter).
    // ---------------------------------------------------------------

    // ---- HS Code search, sourced live from FBR (Section 5.3: `itemdesccode`) ----
    // That endpoint returns the *entire* national HS Code list (confirmed
    // live: 7,800+ rows) with no server-side filtering — too many for a
    // plain <select>, and exactly the case Section 28 calls out
    // ("searchable HS Codes... debounce... search"). Fetched once per
    // page load (Laravel caches the live FBR response for 12h server-side
    // too, so this never hammers FBR itself), then filtered entirely in
    // the browser as the user types — no per-keystroke network call.

    let hsCodesList = [];
    let selectedHsCode = null; // the confirmed code backing the current entry row, or null while the user is still typing/searching
    let hsCodeSearchTimer = null;

    function loadHsCodesList() {
        setStatus('entry-hs_code-status', 'Loading HS Codes from FBR...');

        return fetchReference(referenceUrls.itemCodes).then(({ ok, body }) => {
            const rows = ok && Array.isArray(body) ? body : null;

            if (rows && rows.length > 0) {
                hsCodesList = rows.map((row) => ({ code: row.hS_CODE, description: row.description })).filter((row) => row.code);
                setStatus('entry-hs_code-status', `${hsCodesList.length} HS Codes loaded live from FBR.`, 'text-success');
            } else {
                hsCodesList = staticHsCodes;
                setStatus('entry-hs_code-status', ((! ok && body && body.message) || 'Unable to load HS Codes from FBR.') + ' Showing the offline list instead.', 'text-warning');
                addRetryLink('entry-hs_code-status', loadHsCodesList);
            }
        }).catch(() => {
            hsCodesList = staticHsCodes;
            setStatus('entry-hs_code-status', 'Unable to reach FBR. Showing the offline HS Code list instead.', 'text-warning');
            addRetryLink('entry-hs_code-status', loadHsCodesList);
        });
    }

    function renderHsCodeSuggestions(query) {
        const box = $('entry-hs_code-suggestions');
        const needle = query.trim().toLowerCase();

        if (needle === '') {
            box.style.display = 'none';
            box.innerHTML = '';
            return;
        }

        const matches = hsCodesList
            .filter((row) => row.code.toLowerCase().includes(needle) || (row.description || '').toLowerCase().includes(needle))
            .slice(0, 50);

        if (matches.length === 0) {
            box.innerHTML = '<div class="list-group-item small text-muted">No matching HS Code.</div>';
            box.style.display = '';
            return;
        }

        box.innerHTML = matches.map((row, i) => (
            `<button type="button" class="list-group-item list-group-item-action small" data-hs-index="${i}">` +
            `<strong>${escapeHtml(row.code)}</strong> &mdash; ${escapeHtml((row.description || '').slice(0, 90))}` +
            '</button>'
        )).join('');
        box.style.display = '';

        matches.forEach((row, i) => {
            box.querySelector(`[data-hs-index="${i}"]`).addEventListener('click', () => selectHsCode(row.code, row.description));
        });
    }

    // Drives loadUom() directly rather than via a dispatched "change"
    // event — entry-hs_code is a plain text input now (not a <select>),
    // and this is the only place its value is ever set, so there is no
    // separate listener to keep in sync. Callers that need a specific
    // UOM preselected (the master-item prefill) set
    // `$('entry-uom').dataset.pendingValue` before calling this.
    function selectHsCode(code, description) {
        selectedHsCode = code;
        $('entry-hs_code').value = code;
        $('entry-hs_code-description').textContent = description || '';
        $('entry-hs_code-suggestions').style.display = 'none';
        loadUom(code);
    }

    $('entry-hs_code').addEventListener('input', function () {
        selectedHsCode = null;
        $('entry-hs_code-description').textContent = '';
        clearTimeout(hsCodeSearchTimer);
        const query = this.value;
        // ~350ms debounce (Section 28's suggested 300-500ms range) — the
        // filtering itself is instant (in-memory), this just avoids
        // re-rendering the suggestion list on every single keystroke.
        hsCodeSearchTimer = setTimeout(() => renderHsCodeSuggestions(query), 350);
    });

    $('entry-hs_code').addEventListener('focus', function () {
        if (this.value.trim() !== '' && ! selectedHsCode) {
            renderHsCodeSuggestions(this.value);
        }
    });

    document.addEventListener('click', function (event) {
        if (! $('entry-hs_code-suggestions').contains(event.target) && event.target !== $('entry-hs_code')) {
            $('entry-hs_code-suggestions').style.display = 'none';
        }
    });

    // ---- HS Code -> UOM (Section 5.9 / 6.2) ----

    let uomSeq = 0;

    function populateUomOptions(options) {
        const select = $('entry-uom');
        const pending = select.dataset.pendingValue || select.value;
        select.innerHTML = '<option value="">Select UoM</option>' +
            options.map((o) => `<option value="${escapeHtml(o)}">${escapeHtml(o)}</option>`).join('');
        select.disabled = false;
        if (pending && options.includes(pending)) {
            select.value = pending;
        }
        delete select.dataset.pendingValue;
    }

    function loadUom(hsCode) {
        const mySeq = ++uomSeq;
        const select = $('entry-uom');

        if (!hsCode) {
            select.innerHTML = '<option value="">Select HS Code first</option>';
            select.disabled = true;
            setStatus('entry-uom-status', '');
            return;
        }

        select.disabled = true;
        select.innerHTML = '<option value="">Loading...</option>';
        setStatus('entry-uom-status', 'Loading UOM from FBR...');

        fetchReference(referenceUrls.hsUom, { hs_code: hsCode }).then(({ ok, body }) => {
            if (mySeq !== uomSeq) return; // a newer HS Code change has already superseded this

            if (!ok) {
                populateUomOptions(staticUomOptions);
                setStatus('entry-uom-status', (body && body.message) || 'Unable to load UOM from FBR. Showing the full offline list.', 'text-warning');
                addRetryLink('entry-uom-status', () => loadUom(hsCode));
                return;
            }

            const options = (Array.isArray(body) ? body : []).map((row) => row.description).filter(Boolean);

            if (options.length === 0) {
                select.innerHTML = '<option value="">No valid UOM for this HS Code</option>';
                select.disabled = true;
                setStatus('entry-uom-status', 'No valid UOM found for this HS Code.', 'text-danger');
                return;
            }

            populateUomOptions(options);
            setStatus('entry-uom-status', 'UOM loaded live from FBR for this HS Code.', 'text-success');
        }).catch(() => {
            if (mySeq !== uomSeq) return;
            populateUomOptions(staticUomOptions);
            setStatus('entry-uom-status', 'Unable to reach FBR. Showing the full offline list.', 'text-warning');
            addRetryLink('entry-uom-status', () => loadUom(hsCode));
        });
    }

    // ---- Sale Type -> Rate (Section 5.8 / 6.2) ----

    let rateSeq = 0;
    let selectedRateId = null; // FBR ratE_ID, only set when a *live* Rate is chosen — drives SRO below.

    function applyPendingRate(select) {
        const pending = select.dataset.pendingValue;
        if (!pending) return;
        const match = Array.from(select.options).find((o) => Number(o.value) === Number(pending));
        if (match) {
            select.value = match.value;
            selectedRateId = match.dataset.rateId ? parseInt(match.dataset.rateId, 10) : null;
        }
        delete select.dataset.pendingValue;
    }

    function loadRate(saleType) {
        const mySeq = ++rateSeq;
        const select = $('entry-rate');
        const date = $('invoice_date').value;

        selectedRateId = null;
        resetSroFields();

        if (!saleType) {
            select.innerHTML = staticRateOptionsHtml;
            setStatus('entry-rate-status', '');
            return;
        }

        if (!date) {
            select.innerHTML = staticRateOptionsHtml;
            applyPendingRate(select);
            setStatus('entry-rate-status', 'Set the Invoice Date to look up a live FBR Rate list; showing manual list for now.', 'text-muted');
            return;
        }

        setStatus('entry-rate-status', 'Checking FBR for a live Rate list...');

        fetchReference(referenceUrls.rates, { sale_type: saleType, date: date }).then(({ ok, body }) => {
            if (mySeq !== rateSeq) return; // a newer Sale Type change has already superseded this

            if (!ok || body.resolvable === false) {
                select.innerHTML = staticRateOptionsHtml;
                applyPendingRate(select);
                setStatus('entry-rate-status', (body && body.message) || 'Live FBR Rate lookup unavailable for this Sale Type; enter Rate manually.', 'text-muted');
                return;
            }

            const rates = body.rates || [];

            if (rates.length === 0) {
                select.innerHTML = staticRateOptionsHtml;
                applyPendingRate(select);
                setStatus('entry-rate-status', 'FBR returned no Rates for this Sale Type; showing the manual list instead.', 'text-warning');
                return;
            }

            select.innerHTML = '<option value="">Select Rate (%)</option>' + rates.map((r) => (
                `<option value="${Number(r.ratE_VALUE)}" data-rate-id="${r.ratE_ID}">${escapeHtml(r.ratE_DESC)}</option>`
            )).join('');
            applyPendingRate(select);
            setStatus('entry-rate-status', 'Live Rate list loaded from FBR.', 'text-success');
        }).catch(() => {
            if (mySeq !== rateSeq) return;
            select.innerHTML = staticRateOptionsHtml;
            applyPendingRate(select);
            setStatus('entry-rate-status', 'Unable to reach FBR. Showing the manual Rate list instead.', 'text-warning');
            addRetryLink('entry-rate-status', () => loadRate(saleType));
        });
    }

    // ---- Rate -> SRO/Schedule -> SRO Item (Section 5.7 / 5.10 / 6.2) ----
    // Only reachable once a *live* Rate (carrying a real FBR ratE_ID) is
    // selected — SRO's own required input (rate_id) doesn't exist
    // otherwise, so falling back to free-text entry here is correct, not
    // a compromise (Section 24 Rule 2 applied one level up the chain).

    let sroSeq = 0;
    let sroItemSeq = 0;
    let selectedSroId = null;

    function setSroScheduleMode(mode, options) {
        const wrap = $('entry-sro_schedule_no-wrap');
        if (mode === 'select' && options && options.length) {
            wrap.innerHTML = '<select id="entry-sro_schedule_no" class="form-select">' +
                '<option value="">Select SRO/Schedule (optional)</option>' +
                options.map((o) => `<option value="${escapeHtml(o.srO_DESC)}" data-sro-id="${o.srO_ID}">${escapeHtml(o.srO_DESC)}</option>`).join('') +
                '</select>';
            $('entry-sro_schedule_no').addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                selectedSroId = opt && opt.dataset.sroId ? parseInt(opt.dataset.sroId, 10) : null;
                setSroItemMode('text');
                setStatus('entry-sro_item_sr_no-status', '');
                loadSroItems();
            });
        } else {
            wrap.innerHTML = '<input type="text" id="entry-sro_schedule_no" class="form-control">';
        }
    }

    function setSroItemMode(mode, options) {
        const wrap = $('entry-sro_item_sr_no-wrap');
        if (mode === 'select' && options && options.length) {
            wrap.innerHTML = '<select id="entry-sro_item_sr_no" class="form-select">' +
                '<option value="">Select SRO Item (optional)</option>' +
                options.map((o) => `<option value="${escapeHtml(o.srO_ITEM_DESC)}">${escapeHtml(o.srO_ITEM_DESC)}</option>`).join('') +
                '</select>';
        } else {
            wrap.innerHTML = '<input type="text" id="entry-sro_item_sr_no" class="form-control">';
        }
    }

    function resetSroFields() {
        selectedSroId = null;
        setSroScheduleMode('text');
        setSroItemMode('text');
        setStatus('entry-sro_schedule_no-status', '');
        setStatus('entry-sro_item_sr_no-status', '');
    }

    function loadSro() {
        const mySeq = ++sroSeq;
        selectedSroId = null;
        setSroItemMode('text');
        setStatus('entry-sro_item_sr_no-status', '');

        if (!selectedRateId) {
            setSroScheduleMode('text');
            setStatus('entry-sro_schedule_no-status', '');
            return;
        }

        const date = $('invoice_date').value;
        setStatus('entry-sro_schedule_no-status', 'Checking FBR for an applicable SRO/Schedule...');

        fetchReference(referenceUrls.sro, { rate_id: selectedRateId, date: date }).then(({ ok, body }) => {
            if (mySeq !== sroSeq) return;

            if (!ok) {
                setSroScheduleMode('text');
                setStatus('entry-sro_schedule_no-status', (body && body.message) || 'Unable to load SRO from FBR; enter manually if applicable.', 'text-warning');
                addRetryLink('entry-sro_schedule_no-status', loadSro);
                return;
            }

            if (!Array.isArray(body) || body.length === 0) {
                setSroScheduleMode('text');
                setStatus('entry-sro_schedule_no-status', 'No SRO/Schedule applicable for this Rate — optional, leave blank if none.', 'text-muted');
                return;
            }

            setSroScheduleMode('select', body);
            setStatus('entry-sro_schedule_no-status', 'Live SRO list loaded from FBR (optional).', 'text-success');
        }).catch(() => {
            if (mySeq !== sroSeq) return;
            setSroScheduleMode('text');
            setStatus('entry-sro_schedule_no-status', 'Unable to reach FBR; enter SRO manually if applicable.', 'text-warning');
            addRetryLink('entry-sro_schedule_no-status', loadSro);
        });
    }

    function loadSroItems() {
        const mySeq = ++sroItemSeq;

        if (!selectedSroId) {
            setSroItemMode('text');
            return;
        }

        const date = $('invoice_date').value;
        setStatus('entry-sro_item_sr_no-status', 'Loading SRO Items from FBR...');

        fetchReference(referenceUrls.sroItems, { sro_id: selectedSroId, date: date }).then(({ ok, body }) => {
            if (mySeq !== sroItemSeq) return;

            if (!ok || !Array.isArray(body) || body.length === 0) {
                setSroItemMode('text');
                setStatus('entry-sro_item_sr_no-status', (!ok && body && body.message) || 'No SRO Item list available — optional, leave blank if none.', 'text-muted');
                return;
            }

            setSroItemMode('select', body);
            setStatus('entry-sro_item_sr_no-status', 'Live SRO Item list loaded from FBR.', 'text-success');
        }).catch(() => {
            if (mySeq !== sroItemSeq) return;
            setSroItemMode('text');
            setStatus('entry-sro_item_sr_no-status', 'Unable to reach FBR; enter SRO Item manually if applicable.', 'text-warning');
        });
    }

    // ---- Live scenario preview (Section 7/8) ----
    // Reuses the exact same FbrScenarioResolver the real submit path
    // enforces (via the resolve-scenario endpoint) so this preview can
    // never drift from what actually happens at submission time.

    function refreshScenarioPreview() {
        const box = $('scenario-preview');
        const saleTypes = Array.from(new Set(items.map((i) => i.sale_type).filter(Boolean)));
        const buyerType = $('buyer_registration_type').value;

        if (saleTypes.length === 0 || !buyerType) {
            box.style.display = 'none';
            return;
        }

        fetch(referenceUrls.resolveScenario, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ sale_types: saleTypes, buyer_registration_type: buyerType }),
        }).then((res) => res.json()).then((body) => {
            box.style.display = '';
            if (body.conflict) {
                box.className = 'alert alert-danger small mt-3';
                box.textContent = 'These items map to more than one FBR scenario; FBR only accepts one scenario per invoice. Split them across separate invoices.';
            } else if (!body.scenarioId) {
                box.className = 'alert alert-warning small mt-3';
                box.textContent = 'No FBR Sandbox scenario is mapped yet for one or more of these Sale Types.';
            } else {
                box.className = 'alert alert-secondary small mt-3';
                box.textContent = 'Resolved FBR Scenario: ' + body.scenarioId;
            }
        }).catch(() => {
            box.style.display = 'none';
        });
    }

    $('entry-sale_type').addEventListener('change', function () {
        delete $('entry-rate').dataset.pendingValue;
        loadRate(this.value);
    });

    $('invoice_date').addEventListener('change', function () {
        if ($('entry-sale_type').value) {
            loadRate($('entry-sale_type').value);
        }
    });

    // Standard sales-tax formulas only — see InvoiceCalculator's docblock
    // for exactly what is and isn't included. This mirrors the backend
    // purely for live UX feedback; the server always recomputes
    // authoritatively before saving.
    function recalcEntry() {
        const pricePerUnit = num('entry-price_per_unit');
        const quantity = num('entry-quantity');
        const rate = num('entry-rate');

        if (pricePerUnit > 0) {
            $('entry-value_sales_excl_st').value = (pricePerUnit * quantity).toFixed(2);
        }

        const valueExclSt = num('entry-value_sales_excl_st');
        $('entry-sales_tax').value = (valueExclSt * rate / 100).toFixed(2);

        if (!$('entry-manual-further-tax').checked) {
            $('entry-further_tax').value = '0.00';
        }

        const salesTax = num('entry-sales_tax');
        const furtherTax = num('entry-further_tax');
        const extraTax = num('entry-extra_tax');
        const fedPayable = num('entry-fed_payable');
        const discount = num('entry-discount');

        $('entry-total_sales_value').value = (valueExclSt + salesTax + furtherTax + extraTax + fedPayable - discount).toFixed(2);
    }

    [
        'entry-price_per_unit', 'entry-quantity', 'entry-rate', 'entry-value_sales_excl_st',
        'entry-further_tax', 'entry-extra_tax', 'entry-fed_payable', 'entry-discount',
    ].forEach((id) => $(id).addEventListener('input', recalcEntry));
    $('entry-rate').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        selectedRateId = opt && opt.dataset.rateId ? parseInt(opt.dataset.rateId, 10) : null;
        loadSro();
        recalcEntry();
    });

    $('entry-manual-further-tax').addEventListener('change', function () {
        $('entry-further_tax').readOnly = !this.checked;
        if (!this.checked) {
            $('entry-further_tax').value = '0.00';
        }
        recalcEntry();
    });

    $('item-select').addEventListener('change', function () {
        const item = itemsMaster.find((i) => String(i.id) === this.value);
        if (!item) return;
        $('entry-sale_type').value = item.sale_type || '';
        $('entry-product_description').value = item.description || '';
        $('entry-price_per_unit').value = item.price_per_unit || '';

        // Master data's HS Code/Sale Type still have to go through the
        // same live FBR lookups as a manual selection would — carried
        // through as a "pending" value the load functions apply once
        // their fetch resolves, so the prefilled UOM/Rate only ever end
        // up selected if FBR (or the offline fallback list) actually
        // offers them (Section 6.2 rule 4: never allow a value that
        // wasn't actually returned for the current parent).
        $('entry-uom').dataset.pendingValue = item.uom || '';

        if (item.hs_code) {
            // selectHsCode() itself calls loadUom(), reusing the same
            // "pendingValue" reconciliation as a manual search selection
            // would — no separate loadUom() call needed here.
            const known = hsCodesList.find((row) => row.code === item.hs_code);
            selectHsCode(item.hs_code, known ? known.description : '');
        } else {
            loadUom('');
        }

        $('entry-rate').dataset.pendingValue = item.rate || '';
        loadRate(item.sale_type || '');

        recalcEntry();
    });

    $('customer-select').addEventListener('change', function () {
        $('customer_id').value = this.value || '';
        const customer = customersMaster.find((c) => String(c.id) === this.value);
        if (!customer) return;
        $('buyer_ntn_cnic').value = customer.ntn_cnic || '';
        $('buyer_business_name').value = customer.business_name || '';
        $('buyer_address').value = customer.address || '';
        $('buyer_registration_type').value = customer.registration_type || '';
        $('buyer_province').value = customer.province || '';
        $('buyer_strn').value = customer.strn || '';
        syncBuyerRegistrationTypeUi();
    });

    // ---- Buyer Registration Type change behavior (Section 17) ----

    let lastBuyerRegType = $('buyer_registration_type').value;

    function syncBuyerRegistrationTypeUi() {
        const isRegistered = $('buyer_registration_type').value === 'registered';
        $('buyer_ntn_cnic').required = isRegistered;
        const mark = $('buyer_ntn_cnic-required-mark');
        if (mark) mark.style.display = isRegistered ? '' : 'none';
        refreshScenarioPreview();
    }

    $('buyer_registration_type').addEventListener('change', function () {
        // Only clear a previously-entered NTN/CNIC when the type actually
        // *changes* (e.g. after a customer prefill left a Registered
        // buyer's NTN in place and the user then switches to
        // Unregistered) — never on initial page load, and never just for
        // re-selecting the same value (Section 17: "do not leave stale
        // values from the previous buyer type").
        if (this.value !== lastBuyerRegType) {
            $('buyer_ntn_cnic').value = '';
        }
        lastBuyerRegType = this.value;
        syncBuyerRegistrationTypeUi();
    });

    function renderHiddenInputs() {
        const container = $('items-hidden-inputs');
        container.innerHTML = '';
        items.forEach((item, index) => {
            Object.keys(item).forEach((key) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${index}][${key}]`;
                input.value = item[key] ?? '';
                container.appendChild(input);
            });
        });
    }

    function renderItems() {
        const tbody = $('items-tbody');
        tbody.innerHTML = '';
        let totalExclSt = 0, totalSalesTax = 0, totalValue = 0;

        items.forEach((item, index) => {
            totalExclSt += parseFloat(item.value_sales_excl_st) || 0;
            totalSalesTax += parseFloat(item.sales_tax) || 0;
            totalValue += parseFloat(item.total_sales_value) || 0;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${escapeHtml(item.sale_type)}</td>
                <td>${escapeHtml(item.hs_code)}</td>
                <td>${escapeHtml(item.product_description)}</td>
                <td>${Number(item.rate).toFixed(2)}%</td>
                <td>${escapeHtml(item.uom)}</td>
                <td>${Number(item.quantity)}</td>
                <td>${Number(item.price_per_unit || 0).toFixed(2)}</td>
                <td>${Number(item.value_sales_excl_st).toFixed(2)}</td>
                <td>${Number(item.sales_tax).toFixed(2)}</td>
                <td>${Number(item.total_sales_value).toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">Remove</button></td>
            `;
            tbody.appendChild(row);
        });

        $('totals-excl-st').textContent = totalExclSt.toFixed(2);
        $('totals-sales-tax').textContent = totalSalesTax.toFixed(2);
        $('totals-total').textContent = totalValue.toFixed(2);

        renderHiddenInputs();
        refreshScenarioPreview();

        tbody.querySelectorAll('.remove-item').forEach((btn) => {
            btn.addEventListener('click', function () {
                items.splice(parseInt(this.dataset.index, 10), 1);
                renderItems();
            });
        });
    }

    function resetEntryRow() {
        [
            'entry-product_description', 'entry-price_per_unit', 'entry-quantity',
            'entry-value_sales_excl_st', 'entry-fixed_retail_price', 'entry-st_withheld_at_source',
            'entry-extra_tax', 'entry-fed_payable', 'entry-discount',
        ].forEach((id) => { $(id).value = ''; });

        $('entry-sale_type').value = '';
        $('entry-hs_code').value = '';
        $('entry-hs_code-description').textContent = '';
        $('entry-hs_code-suggestions').style.display = 'none';
        selectedHsCode = null;
        // loadUom('')/loadRate('') put UOM/Rate/SRO/SRO Item back to their
        // disabled/placeholder/static-fallback starting state — the same
        // reset the HS Code/Sale Type "change to empty" path would do,
        // reused here instead of duplicating it.
        loadUom('');
        loadRate('');
        $('item-select').value = '';
        $('entry-sales_tax').value = '0.00';
        $('entry-total_sales_value').value = '0.00';
        $('entry-manual-further-tax').checked = false;
        $('entry-further_tax').readOnly = true;
        $('entry-further_tax').value = '0.00';
    }

    $('add-item-btn').addEventListener('click', function () {
        recalcEntry();

        if (!$('entry-sale_type').value || !selectedHsCode || !$('entry-product_description').value
            || !$('entry-rate').value || !$('entry-uom').value || !num('entry-quantity')) {
            alert('Please fill in Sale Type, HS Code (selected from the suggestions list), Product Description, Rate, UoM and Quantity before adding the item.');
            return;
        }

        items.push({
            item_id: $('item-select').value || null,
            sale_type: $('entry-sale_type').value,
            hs_code: $('entry-hs_code').value,
            product_description: $('entry-product_description').value,
            rate: num('entry-rate'),
            uom: $('entry-uom').value,
            price_per_unit: num('entry-price_per_unit'),
            quantity: num('entry-quantity'),
            value_sales_excl_st: num('entry-value_sales_excl_st'),
            sales_tax: num('entry-sales_tax'),
            further_tax: num('entry-further_tax'),
            fixed_retail_price: num('entry-fixed_retail_price'),
            st_withheld_at_source: num('entry-st_withheld_at_source'),
            extra_tax: num('entry-extra_tax'),
            fed_payable: num('entry-fed_payable'),
            discount: num('entry-discount'),
            total_sales_value: num('entry-total_sales_value'),
            sro_schedule_no: $('entry-sro_schedule_no').value || null,
            sro_item_sr_no: $('entry-sro_item_sr_no').value || null,
        });

        renderItems();
        resetEntryRow();
    });

    $('save-draft-btn').addEventListener('click', function () {
        $('invoice-action').value = 'draft';
    });
    $('submit-invoice-btn').addEventListener('click', function () {
        $('invoice-action').value = 'submit';
    });

    $('preview-btn').addEventListener('click', function () {
        const rows = items.map((item) => `
            <tr>
                <td>${escapeHtml(item.product_description)}</td>
                <td>${escapeHtml(item.hs_code)}</td>
                <td>${Number(item.quantity)}</td>
                <td>${Number(item.value_sales_excl_st).toFixed(2)}</td>
                <td>${Number(item.sales_tax).toFixed(2)}</td>
                <td>${Number(item.total_sales_value).toFixed(2)}</td>
            </tr>
        `).join('');

        $('preview-body').innerHTML = `
            <p><strong>Buyer:</strong> ${escapeHtml($('buyer_business_name').value)} (${escapeHtml($('buyer_ntn_cnic').value)})</p>
            <p><strong>Address:</strong> ${escapeHtml($('buyer_address').value)}</p>
            <table class="table table-sm">
                <thead>
                    <tr><th>Description</th><th>HS Code</th><th>Qty</th><th>Value Excl. ST</th><th>Sales Tax</th><th>Total</th></tr>
                </thead>
                <tbody>${rows || '<tr><td colspan="6" class="text-center text-muted">No items added yet.</td></tr>'}</tbody>
            </table>
        `;

        new bootstrap.Modal($('preview-modal')).show();
    });

    syncBuyerRegistrationTypeUi();
    renderItems();
    loadProvinces();
    loadInvoiceTypes();
    loadSaleTypesList();
    loadHsCodesList();
})();
</script>
@endpush
