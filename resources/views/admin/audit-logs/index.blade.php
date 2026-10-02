@extends('layouts.admin')

@section('title', 'System Audit Logs')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">System Audit Logs</h1>
            <p class="text-muted small mb-0">Immutable records of system actions, company updates, and user activities.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Performed By</th>
                        <th>Action Code</th>
                        <th>Module</th>
                        <th>Company</th>
                        <th>IP Address</th>
                        <th class="text-end">Payload</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php($hasDetails = $log->description || $log->old_values || $log->new_values)
                        <tr>
                            <td class="text-muted small">{{ $log->created_at->format('d-M-Y H:i:s') }}</td>
                            <td class="fw-bold text-dark">{{ $log->user?->name ?? 'System Automated' }}</td>
                            <td><code class="text-primary fs-6">{{ $log->action }}</code></td>
                            <td><span class="badge bg-slate-100 text-dark border">{{ $log->module }}</span></td>
                            <td>{{ $log->company?->name ?? '—' }}</td>
                            <td class="font-monospace small text-muted">{{ $log->ip_address ?? '—' }}</td>
                            <td class="text-end">
                                @if ($hasDetails)
                                    <button type="button" class="btn btn-sm btn-light border text-dark fw-medium" data-bs-toggle="collapse" data-bs-target="#log-{{ $log->id }}">
                                        <i class="bi bi-code-square me-1"></i> Details
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @if ($hasDetails)
                            <tr class="collapse" id="log-{{ $log->id }}">
                                <td colspan="7" class="bg-slate-100 p-3">
                                    <div class="bg-white p-3 rounded border">
                                        @if ($log->description)
                                            <p class="mb-2 fw-semibold text-dark">{{ $log->description }}</p>
                                        @endif
                                        <div class="row g-3">
                                            @if ($log->old_values)
                                                <div class="col-md-6">
                                                    <div class="extra-small text-uppercase fw-bold text-muted mb-1">Previous State</div>
                                                    <pre class="bg-slate-900 text-slate-200 p-2.5 rounded small mb-0 font-monospace" style="max-height: 200px; overflow-y: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            @endif
                                            @if ($log->new_values)
                                                <div class="col-md-6">
                                                    <div class="extra-small text-uppercase fw-bold text-muted mb-1">New State</div>
                                                    <pre class="bg-slate-900 text-slate-200 p-2.5 rounded small mb-0 font-monospace" style="max-height: 200px; overflow-y: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-journal-text display-6 text-slate-300 d-block mb-2"></i>
                                No audit log entries recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
@endsection
