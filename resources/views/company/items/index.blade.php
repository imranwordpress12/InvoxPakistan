@extends('layouts.company')

@section('title', 'Items')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Items</h1>
        <a href="{{ route('company.items.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Create Item
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('company.items.index') }}" class="row g-2">
                <div class="col-md-8">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Search by item name or code"
                    >
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Type</th>
                        <th>Sale Type</th>
                        <th>HS Code</th>
                        <th>Rate (%)</th>
                        <th>Sale Price</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ ucfirst($item->item_type) }}</td>
                            <td>{{ $item->sale_type ?? '—' }}</td>
                            <td>{{ $item->hs_code ?? '—' }}</td>
                            <td>{{ $item->rate !== null ? number_format($item->rate, 2).'%' : '—' }}</td>
                            <td>{{ $item->sale_price !== null ? 'Rs '.number_format($item->sale_price, 2) : '—' }}</td>
                            <td><x-status-badge :status="$item->status" /></td>
                            <td class="text-end">
                                <a href="{{ route('company.items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No items yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $items->links() }}
    </div>
@endsection
