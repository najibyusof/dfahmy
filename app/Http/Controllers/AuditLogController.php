<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $filters = [
            'action' => (string) $request->string('action'),
            'subject_type' => (string) $request->string('subject_type'),
            'search' => (string) $request->string('search'),
            'from' => (string) $request->string('from'),
            'to' => (string) $request->string('to'),
        ];

        $logs = AuditLog::query()
            ->with('user:id,name')
            ->when($filters['action'] !== '', function ($query) use ($filters): void {
                $query->where('action', $filters['action']);
            })
            ->when($filters['subject_type'] !== '', function ($query) use ($filters): void {
                $query->where('subject_type', $filters['subject_type']);
            })
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($subQuery) use ($filters): void {
                    $subQuery->where('subject_type', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('subject_id', (int) $filters['search'])
                        ->orWhere('ip_address', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->when($filters['from'] !== '', function ($query) use ($filters): void {
                $query->whereDate('created_at', '>=', $filters['from']);
            })
            ->when($filters['to'] !== '', function ($query) use ($filters): void {
                $query->whereDate('created_at', '<=', $filters['to']);
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $subjectTypes = AuditLog::query()->select('subject_type')->distinct()->orderBy('subject_type')->pluck('subject_type');

        return view('audit-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'subjectTypes' => $subjectTypes,
            'actions' => ['create', 'update', 'delete', 'status_change'],
        ]);
    }
}
