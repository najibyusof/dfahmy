<?php

namespace App\Actions;

use App\Services\RoleAssignmentAuditQueryService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportRoleAssignmentAuditCsvAction
{
    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters, RoleAssignmentAuditQueryService $auditQueryService): StreamedResponse
    {
        $fileName = 'role-assignment-audits-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($filters, $auditQueryService): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['When', 'Changed By', 'Changed By Email', 'Target User', 'Target Email', 'From Role', 'To Role']);

            $auditQueryService->query($filters)
                ->cursor()
                ->each(function ($log) use ($handle): void {
                    fputcsv($handle, [
                        $log->created_at?->format('Y-m-d H:i:s'),
                        $log->actor?->name,
                        $log->actor?->email,
                        $log->target?->name,
                        $log->target?->email,
                        $log->from_role,
                        $log->to_role,
                    ]);
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
