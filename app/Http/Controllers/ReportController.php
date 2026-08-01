<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Services\ReportService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(ReportFilterRequest $request, ReportService $reportService): View
    {
        $this->authorize('viewReports', $request->user());

        $filters = $this->filters($request);
        $reports = $reportService->generate($filters['from'], $filters['to']);

        return view('reports.index', [
            'filters' => $filters,
            'reports' => $reports,
        ]);
    }

    public function export(ReportFilterRequest $request, string $type, ReportService $reportService): StreamedResponse
    {
        $this->authorize('viewReports', $request->user());

        $filters = $this->filters($request);
        $reports = $reportService->generate($filters['from'], $filters['to']);

        if (! array_key_exists($type, $reports)) {
            abort(404);
        }

        $filename = $type . '-' . $filters['from'] . '-to-' . $filters['to'] . '.csv';
        $payload = $reports[$type];

        return response()->streamDownload(function () use ($payload): void {
            $handle = fopen('php://output', 'wb');

            if (is_array($payload) && $payload !== [] && isset($payload[0]) && is_array($payload[0])) {
                fputcsv($handle, array_keys($payload[0]));
                foreach ($payload as $row) {
                    fputcsv($handle, $row);
                }
            } elseif (is_array($payload)) {
                fputcsv($handle, ['metric', 'value']);
                foreach ($payload as $metric => $value) {
                    fputcsv($handle, [(string) $metric, (string) $value]);
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array{from: string, to: string}
     */
    private function filters(ReportFilterRequest $request): array
    {
        return $request->dateRange();
    }
}
