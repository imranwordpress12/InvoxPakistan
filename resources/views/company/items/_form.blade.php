@php
    $item = $item ?? null;
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
    action="{{ $item ? route('company.items.update', $item) : route('company.items.store') }}"
>
    @csrf
    @if ($item)
        @method('PUT')
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Item Code <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="item_code"
                    value="{{ old('item_code', $item?->item_code) }}"
                    class="form-control @error('item_code') is-invalid @enderror"
                    required
                >
                @error('item_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="item_name"
                    value="{{ old('item_name', $item?->item_name) }}"
                    class="form-control @error('item_name') is-invalid @enderror"
                    required
                >
                @error('item_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Item Type <span class="text-danger">*</span></label>
                <select name="item_type" class="form-select @error('item_type') is-invalid @enderror" required>
                    <option value="goods" @selected(old('item_type', $item?->item_type ?? 'goods') === 'goods')>Goods</option>
                    <option value="service" @selected(old('item_type', $item?->item_type) === 'service')>Service</option>
                </select>
                @error('item_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Sale Type</label>
                <select name="sale_type" id="item-sale_type" class="form-select @error('sale_type') is-invalid @enderror">
                    <option value="">Select Sale Type</option>
                    @foreach ($saleTypes as $saleType)
                        <option value="{{ $saleType }}" @selected(old('sale_type', $item?->sale_type) === $saleType)>{{ $saleType }}</option>
                    @endforeach
                </select>
                <div class="form-text small" id="item-sale_type-status"></div>
                @error('sale_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">HS Code</label>
                <div class="position-relative">
                    <input
                        type="text"
                        name="hs_code"
                        id="item-hs_code"
                        value="{{ old('hs_code', $item?->hs_code) }}"
                        class="form-control @error('hs_code') is-invalid @enderror"
                        placeholder="Type to search HS Code..."
                        autocomplete="off"
                    >
                    <div id="item-hs_code-suggestions" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1000; max-height: 260px; overflow-y: auto; display: none;"></div>
                </div>
                <div class="form-text small" id="item-hs_code-description"></div>
                <div class="form-text small" id="item-hs_code-status"></div>
                @error('hs_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Rate (%)</label>
                <select name="rate" id="item-rate" class="form-select @error('rate') is-invalid @enderror">
                    <option value="">Select Rate (%)</option>
                    @foreach ($taxRates as $taxRate)
                        <option value="{{ $taxRate->rate }}" @selected(old('rate', $item?->rate) == $taxRate->rate)>
                            {{ number_format($taxRate->rate, 2) }}%
                        </option>
                    @endforeach
                </select>
                <div class="form-text small" id="item-rate-status"></div>
                @error('rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">UoM</label>
                <select name="uom" id="item-uom" class="form-select @error('uom') is-invalid @enderror" disabled>
                    <option value="">Select HS Code first</option>
                </select>
                <div class="form-text small" id="item-uom-status"></div>
                @error('uom') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Purchase Price</label>
                <input
                    type="number" step="0.01" min="0"
                    name="purchase_price"
                    value="{{ old('purchase_price', $item?->purchase_price) }}"
                    class="form-control @error('purchase_price') is-invalid @enderror"
                >
                @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Sale Price</label>
                <input
                    type="number" step="0.01" min="0"
                    name="sale_price"
                    value="{{ old('sale_price', $item?->sale_price) }}"
                    class="form-control @error('sale_price') is-invalid @enderror"
                >
                @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Stock Quantity</label>
                <input
                    type="number" step="1" min="0"
                    name="stock_quantity"
                    value="{{ old('stock_quantity', $item?->stock_quantity ?? 0) }}"
                    class="form-control @error('stock_quantity') is-invalid @enderror"
                >
                @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Reorder Level</label>
                <input
                    type="number" step="1" min="0"
                    name="reorder_level"
                    value="{{ old('reorder_level', $item?->reorder_level ?? 0) }}"
                    class="form-control @error('reorder_level') is-invalid @enderror"
                >
                @error('reorder_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-9">
                <label class="form-label">Description</label>
                <textarea
                    name="description"
                    rows="2"
                    class="form-control @error('description') is-invalid @enderror"
                >{{ old('description', $item?->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="active" @selected(old('status', $item?->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $item?->status) === 'inactive')>Inactive</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $item ? 'Update Item' : 'Save Item' }}</button>
        <a href="{{ route('company.items.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const saleType = $('item-sale_type');
    const hsCode = $('item-hs_code');
    const suggestions = $('item-hs_code-suggestions');
    const rate = $('item-rate');
    const uom = $('item-uom');
    const staticSaleTypes = saleType.innerHTML;
    const staticRates = rate.innerHTML;
    const staticUoms = @json($unitsOfMeasure);
    const staticHsCodes = @json($hsCodes->map(fn ($h) => ['code' => $h->code, 'description' => $h->description]));
    const oldRate = rate.value;
    const oldUom = @json(old('uom', $item?->uom));
    const referenceUrls = {
        transactionTypes: @json(route('company.invoices.reference.transaction-types')),
        itemCodes: @json(route('company.invoices.reference.item-codes')),
        hsUom: @json(route('company.invoices.reference.hs-uom')),
        rates: @json(route('company.invoices.reference.rates')),
    };
    const referenceDate = @json(now()->toDateString());
    let hsCodes = [];
    let selectedHsCode = hsCode.value || null;
    let uomSequence = 0;
    let rateSequence = 0;

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function status(id, message, cssClass) {
        const element = $(id);
        element.textContent = message || '';
        element.className = 'form-text small' + (cssClass ? ' ' + cssClass : '');
    }

    function retry(id, callback) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-link btn-sm p-0 align-baseline';
        button.textContent = 'Retry';
        button.addEventListener('click', callback);
        $(id).append(' ', button);
    }

    function fetchReference(url, params) {
        const query = params ? '?' + new URLSearchParams(params).toString() : '';
        return fetch(url + query, { headers: { Accept: 'application/json' } })
            .then((response) => response.json().catch(() => ({})).then((body) => ({ ok: response.ok, body })));
    }

    function loadSaleTypes() {
        status('item-sale_type-status', 'Loading Sale Types from FBR...');
        return fetchReference(referenceUrls.transactionTypes).then(({ ok, body }) => {
            const rows = ok && Array.isArray(body) ? body.filter((row) => row.transactioN_DESC) : [];
            if (!rows.length) {
                saleType.innerHTML = staticSaleTypes;
                status('item-sale_type-status', (body.message || 'Unable to load Sale Types from FBR.') + ' Showing the offline list instead.', 'text-warning');
                retry('item-sale_type-status', loadSaleTypes);
                return;
            }
            saleType.innerHTML = '<option value="">Select Sale Type</option>' + rows.map((row) => `<option value="${escapeHtml(row.transactioN_DESC)}">${escapeHtml(row.transactioN_DESC)}</option>`).join('');
            status('item-sale_type-status', `${rows.length} Sale Types loaded live from FBR.`, 'text-success');
        }).catch(() => {
            saleType.innerHTML = staticSaleTypes;
            status('item-sale_type-status', 'Unable to reach FBR. Showing the offline Sale Type list instead.', 'text-warning');
            retry('item-sale_type-status', loadSaleTypes);
        });
    }

    function renderSuggestions(query) {
        const needle = query.trim().toLowerCase();
        if (!needle) {
            suggestions.style.display = 'none';
            suggestions.innerHTML = '';
            return;
        }
        const matches = hsCodes.filter((row) => row.code.toLowerCase().includes(needle) || (row.description || '').toLowerCase().includes(needle)).slice(0, 50);
        suggestions.innerHTML = matches.length
            ? matches.map((row, index) => `<button type="button" class="list-group-item list-group-item-action small" data-index="${index}"><strong>${escapeHtml(row.code)}</strong> &mdash; ${escapeHtml((row.description || '').slice(0, 90))}</button>`).join('')
            : '<div class="list-group-item small text-muted">No matching HS Code.</div>';
        suggestions.style.display = '';
        matches.forEach((row, index) => suggestions.querySelector(`[data-index="${index}"]`).addEventListener('click', () => selectHsCode(row.code, row.description)));
    }

    function selectHsCode(code, description) {
        selectedHsCode = code;
        hsCode.value = code;
        $('item-hs_code-description').textContent = description || '';
        suggestions.style.display = 'none';
        loadUom(code);
    }

    function loadHsCodes() {
        status('item-hs_code-status', 'Loading HS Codes from FBR...');
        return fetchReference(referenceUrls.itemCodes).then(({ ok, body }) => {
            hsCodes = ok && Array.isArray(body) ? body.map((row) => ({ code: row.hS_CODE, description: row.description })).filter((row) => row.code) : staticHsCodes;
            status('item-hs_code-status', hsCodes === staticHsCodes ? 'Unable to load HS Codes from FBR. Showing the offline list instead.' : `${hsCodes.length} HS Codes loaded live from FBR.`, hsCodes === staticHsCodes ? 'text-warning' : 'text-success');
            if (hsCodes === staticHsCodes) retry('item-hs_code-status', loadHsCodes);
            const known = hsCodes.find((row) => row.code === hsCode.value);
            if (known) {
                selectedHsCode = known.code;
                $('item-hs_code-description').textContent = known.description || '';
                loadUom(known.code);
            }
        }).catch(() => {
            hsCodes = staticHsCodes;
            status('item-hs_code-status', 'Unable to reach FBR. Showing the offline HS Code list instead.', 'text-warning');
            retry('item-hs_code-status', loadHsCodes);
        });
    }

    function loadUom(code) {
        const sequence = ++uomSequence;
        if (!code) {
            uom.innerHTML = '<option value="">Select HS Code first</option>';
            uom.disabled = true;
            return;
        }
        uom.disabled = true;
        uom.innerHTML = '<option value="">Loading...</option>';
        status('item-uom-status', 'Loading UOM from FBR...');
        fetchReference(referenceUrls.hsUom, { hs_code: code }).then(({ ok, body }) => {
            if (sequence !== uomSequence) return;
            const options = ok && Array.isArray(body) ? body.map((row) => row.description).filter(Boolean) : [];
            if (!options.length) {
                uom.innerHTML = '<option value="">Select UoM</option>' + staticUoms.map((value) => `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`).join('');
                uom.disabled = false;
                uom.value = oldUom || '';
                status('item-uom-status', (body.message || 'Unable to load UOM from FBR.') + ' Showing the offline list instead.', 'text-warning');
                retry('item-uom-status', () => loadUom(code));
                return;
            }
            uom.innerHTML = '<option value="">Select UoM</option>' + options.map((value) => `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`).join('');
            uom.disabled = false;
            if (options.includes(oldUom)) uom.value = oldUom;
            status('item-uom-status', 'UOM loaded live from FBR for this HS Code.', 'text-success');
        }).catch(() => {
            if (sequence !== uomSequence) return;
            uom.innerHTML = '<option value="">Select UoM</option>' + staticUoms.map((value) => `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`).join('');
            uom.disabled = false;
            uom.value = oldUom || '';
            status('item-uom-status', 'Unable to reach FBR. Showing the offline UOM list instead.', 'text-warning');
            retry('item-uom-status', () => loadUom(code));
        });
    }

    function loadRate() {
        const sequence = ++rateSequence;
        const value = saleType.value;
        if (!value) {
            rate.innerHTML = staticRates;
            return;
        }
        rate.innerHTML = '<option value="">Loading...</option>';
        status('item-rate-status', 'Checking FBR for a live Rate list...');
        fetchReference(referenceUrls.rates, { sale_type: value, date: referenceDate }).then(({ ok, body }) => {
            if (sequence !== rateSequence) return;
            const rates = ok && body.resolvable !== false ? (body.rates || []) : [];
            if (!rates.length) {
                rate.innerHTML = staticRates;
                rate.value = oldRate;
                status('item-rate-status', (body.message || 'Live FBR Rate lookup unavailable; showing the offline list instead.'), 'text-warning');
                return;
            }
            rate.innerHTML = '<option value="">Select Rate (%)</option>' + rates.map((row) => `<option value="${Number(row.ratE_VALUE)}">${escapeHtml(row.ratE_DESC)}</option>`).join('');
            rate.value = oldRate;
            status('item-rate-status', 'Live Rate list loaded from FBR.', 'text-success');
        }).catch(() => {
            if (sequence !== rateSequence) return;
            rate.innerHTML = staticRates;
            rate.value = oldRate;
            status('item-rate-status', 'Unable to reach FBR. Showing the offline Rate list instead.', 'text-warning');
            retry('item-rate-status', loadRate);
        });
    }

    saleType.addEventListener('change', loadRate);
    hsCode.addEventListener('input', function () {
        selectedHsCode = null;
        loadUom('');
        renderSuggestions(this.value);
    });
    hsCode.addEventListener('focus', () => renderSuggestions(hsCode.value));
    document.addEventListener('click', (event) => {
        if (!suggestions.contains(event.target) && event.target !== hsCode) suggestions.style.display = 'none';
    });

    Promise.all([loadSaleTypes(), loadHsCodes()]).then(loadRate);
})();
</script>
@endpush
