<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invox Pakistan - Automated Technical SEO Validator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #cbd5e1; font-family: system-ui, sans-serif; }
        .table-dark { --bs-table-bg: #1e293b; --bs-table-color: #cbd5e1; }
        .badge-pass { background-color: #10b981; }
        .badge-fail { background-color: #ef4444; }
        .badge-warn { background-color: #f59e0b; color: #000; }
    </style>
</head>
<body class="py-4">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom border-secondary">
            <div>
                <h1 class="h3 fw-bold text-white mb-1"><i class="bi bi-speedometer2 text-info me-2"></i>Technical SEO &amp; Indexability Audit Dashboard</h1>
                <p class="text-slate-400 mb-0 small">Real-time route inspection for titles, meta descriptions, canonicals, H1 headings, Open Graph, and JSON-LD schemas.</p>
            </div>
            <div>
                <a href="{{ route('public.home') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i> Public Website</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover table-bordered align-middle small">
                <thead>
                    <tr>
                        <th>Route &amp; Path</th>
                        <th>HTTP</th>
                        <th>Title &amp; Length</th>
                        <th>Description &amp; Length</th>
                        <th>Canonical &amp; Robots</th>
                        <th>H1 Heading</th>
                        <th>OG &amp; Twitter</th>
                        <th>Schemas</th>
                        <th>Links</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $res)
                        <tr>
                            <td>
                                <div class="fw-bold text-white">{{ $res['label'] }}</div>
                                <a href="{{ $res['url'] }}" target="_blank" class="text-info text-decoration-none extra-small">{{ $res['path'] }}</a>
                            </td>
                            <td>
                                @if(($res['status'] ?? 0) === 200)
                                    <span class="badge bg-success">200 OK</span>
                                @else
                                    <span class="badge bg-danger">{{ $res['status'] ?? 500 }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 200px;" title="{{ $res['title'] ?? '' }}">{{ $res['title'] ?? 'N/A' }}</div>
                                <div class="text-slate-400 extra-small">{{ $res['title_length'] ?? 0 }} chars</div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 220px;" title="{{ $res['description'] ?? '' }}">{{ $res['description'] ?? 'N/A' }}</div>
                                <div class="text-slate-400 extra-small">{{ $res['desc_length'] ?? 0 }} chars</div>
                            </td>
                            <td>
                                <div class="text-truncate extra-small text-slate-300" style="max-width: 150px;">{{ $res['canonical'] ?? 'N/A' }}</div>
                                <span class="badge bg-secondary">{{ $res['robots'] ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 150px;">{{ $res['h1_text'] ?? 'N/A' }}</div>
                                <span class="badge {{ ($res['h1_count'] ?? 0) === 1 ? 'bg-success' : 'bg-warning text-dark' }}">Count: {{ $res['h1_count'] ?? 0 }}</span>
                            </td>
                            <td>
                                <div class="extra-small">OG: {{ !empty($res['og_title']) ? 'Yes' : 'No' }}</div>
                                <div class="extra-small">Card: {{ $res['twitter_card'] ?? 'No' }}</div>
                            </td>
                            <td>
                                @if(!empty($res['schema_types']))
                                    @foreach($res['schema_types'] as $stype)
                                        <span class="badge bg-info text-dark mb-1">{{ $stype }}</span>
                                    @endforeach
                                @else
                                    <span class="text-slate-500">None</span>
                                @endif
                            </td>
                            <td>
                                <div><i class="bi bi-link-45deg me-1"></i>{{ $res['internal_links'] ?? 0 }}</div>
                            </td>
                            <td>
                                @if($res['passed'])
                                    <span class="badge badge-pass px-2 py-1"><i class="bi bi-check-circle me-1"></i>PASS</span>
                                @else
                                    <span class="badge badge-fail px-2 py-1"><i class="bi bi-x-circle me-1"></i>FAIL</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
