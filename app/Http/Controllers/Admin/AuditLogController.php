<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuditLogs\IndexAuditLogRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(IndexAuditLogRequest $request): View
    {
        $filters = $request->validated();
        $logs = AuditLog::query()
            ->with('actor:id,name,email')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('action', 'like', "%{$search}%")
                        ->orWhere('subject_label', 'like', "%{$search}%")
                        ->orWhere('request_id', 'like', "%{$search}%")
                        ->orWhereHas('actor', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['actor'] ?? null, fn ($query, int $actor) => $query->where('actor_id', $actor))
            ->when($filters['subject_type'] ?? null, fn ($query, string $type) => $query->where('subject_type', $type))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('created_at')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'subjectTypes' => AuditLog::query()->distinct()->orderBy('subject_type')->pluck('subject_type'),
            'actors' => User::query()->whereHas('auditLogs')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        Gate::authorize('view', $auditLog);
        $auditLog->load('actor:id,name,email');

        return view('admin.audit-logs.show', compact('auditLog'));
    }
}
