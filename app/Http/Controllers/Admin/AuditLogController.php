<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * The audit log listing (PRD #33), newest first, 50 per page (PRD #58).
     * Admin-only — a Company must never reach this (PRD #4.2).
     */
    public function index(): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = AuditLog::with(['user', 'company'])
            ->latest()
            ->paginate(50);

        return view('admin.audit-logs.index', ['logs' => $logs]);
    }
}
