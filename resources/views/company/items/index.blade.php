@extends('layouts.company')

@section('title', 'Items & Products')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Items &amp; Product Catalog</h1>
            <p class="text-muted small mb-0">Manage products, HS codes, sales tax rates, and default pricing.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('company.items.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-box-seam-fill me-1"></i> Add New Item
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('company.items.index') }}" class="row g-2">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Search by item name or code...">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Type</th>
                        <th>Sale Type</th>
                        <th>HS Code</th>
                        <th>Tax Rate (%)</th>
                        <th>Sale Price</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td><code class="text-primary fs-6">{{ $item->item_code }}</code></td>
                            <td class="fw-bold text-dark">{{ $item->item_name }}</td>
                            <td><span class="badge bg-slate-100 text-dark border">{{ ucfirst($item->item_type) }}</span></td>
                            <td class="text-muted small">{{ $item->sale_type ?? '—' }}</td>
                            <td class="font-monospace text-slate-700">{{ $item->hs_code ?? '—' }}</td>
                            <td class="fw-semibold text-primary">{{ $item->rate !== null ? number_format($item->rate, 2).'%' : '—' }}</td>
                            <td class="fw-bold text-dark">{{ $item->sale_price !== null ? 'Rs '.number_format($item->sale_price, 2) : '—' }}</td>
                            <td><x-status-badge :status="$item->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('company.items.edit', $item) }}" class="btn btn-sm btn-light border text-dark fw-medium">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-box-seam display-6 text-slate-300 d-block mb-2"></i>
                                No catalog items created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endsection
