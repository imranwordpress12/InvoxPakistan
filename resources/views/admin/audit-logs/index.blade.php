@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
    <h1 class="h4 mb-3">Audit Logs</h1>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Who</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Company</th>
                        <th>IP Address</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php($hasDetails = $log->description || $log->old_values || $log->new_values)
                        <tr>
                            <td>{{ $log->created_at->format('d-M-Y H:i') }}</td>
                            <td>{{ $log->user?->name ?? 'System' }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td>{{ $log->module }}</td>
                            <td>{{ $log->company?->name ?? '—' }}</td>
                            <td>{{ $log->ip_address ?? '—' }}</td>
                            <td>
                                @if ($hasDetails)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#log-{{ $log->id }}"
                                    >
                                        Details
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @if ($hasDetails)
                            <tr class="collapse" id="log-{{ $log->id }}">
                                <td colspan="7" class="bg-light">
                                    @if ($log->description)
                                        <p class="mb-2">{{ $log->description }}</p>
                                    @endif
                                    <div class="row g-3">
                                        @if ($log->old_values)
                                            <div class="col-md-6">
                                                <div class="small text-muted mb-1">Old values</div>
                                                <pre class="small mb-0">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        @endif
                                        @if ($log->new_values)
                                            <div class="col-md-6">
                                                <div class="small text-muted mb-1">New values</div>
                                                <pre class="small mb-0">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No audit log entries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>
@endsection
