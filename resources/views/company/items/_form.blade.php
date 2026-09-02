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
                <select name="sale_type" class="form-select @error('sale_type') is-invalid @enderror">
                    <option value="">Select Sale Type</option>
                    @foreach ($saleTypes as $saleType)
                        <option value="{{ $saleType }}" @selected(old('sale_type', $item?->sale_type) === $saleType)>{{ $saleType }}</option>
                    @endforeach
                </select>
                @error('sale_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">HS Code</label>
                <select name="hs_code" class="form-select @error('hs_code') is-invalid @enderror">
                    <option value="">Select HS Code</option>
                    @foreach ($hsCodes as $hsCode)
                        <option value="{{ $hsCode->code }}" @selected(old('hs_code', $item?->hs_code) === $hsCode->code)>
                            {{ $hsCode->code }} &mdash; {{ $hsCode->description }}
                        </option>
                    @endforeach
                </select>
                @error('hs_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Rate (%)</label>
                <select name="rate" class="form-select @error('rate') is-invalid @enderror">
                    <option value="">Select Rate (%)</option>
                    @foreach ($taxRates as $taxRate)
                        <option value="{{ $taxRate->rate }}" @selected(old('rate', $item?->rate) == $taxRate->rate)>
                            {{ number_format($taxRate->rate, 2) }}%
                        </option>
                    @endforeach
                </select>
                @error('rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">UoM</label>
                <select name="uom" class="form-select @error('uom') is-invalid @enderror">
                    <option value="">Select UoM</option>
                    @foreach ($unitsOfMeasure as $uom)
                        <option value="{{ $uom }}" @selected(old('uom', $item?->uom) === $uom)>{{ $uom }}</option>
                    @endforeach
                </select>
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
