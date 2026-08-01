<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PublicHealthController extends Controller
{
    public function basic(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name', 'application'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function ready(): JsonResponse
    {
        try {
            DB::connection()->getPdo();

            $jobsTableReachable = Schema::hasTable('jobs');
            $failedJobsTableReachable = Schema::hasTable('failed_jobs');

            $ready = $jobsTableReachable && $failedJobsTableReachable;

            return response()->json([
                'status' => $ready ? 'ready' : 'not_ready',
                'timestamp' => now()->toIso8601String(),
                'checks' => [
                    'database' => 'ok',
                    'jobs_table' => $jobsTableReachable ? 'ok' : 'missing',
                    'failed_jobs_table' => $failedJobsTableReachable ? 'ok' : 'missing',
                ],
            ], $ready ? 200 : 503);
        } catch (Throwable) {
            return response()->json([
                'status' => 'not_ready',
                'timestamp' => now()->toIso8601String(),
                'checks' => [
                    'database' => 'unreachable',
                ],
            ], 503);
        }
    }

    public function ops(Request $request): JsonResponse
    {
        $configuredToken = trim((string) config('services.health.token', ''));

        // Hide endpoint when not configured in production environments.
        if ($configuredToken === '') {
            abort(404);
        }

        $suppliedToken = trim((string) $request->header('X-Health-Token', ''));
        if ($suppliedToken === '' || ! hash_equals($configuredToken, $suppliedToken)) {
            return response()->json([
                'status' => 'unauthorized',
                'message' => 'Invalid health token.',
            ], 401);
        }

        $failedJobsCount = Schema::hasTable('failed_jobs')
            ? (int) DB::table('failed_jobs')->count()
            : 0;

        $pendingJobsCount = Schema::hasTable('jobs')
            ? (int) DB::table('jobs')->count()
            : 0;

        $heartbeatRaw = Cache::get('system.scheduler_heartbeat_at');
        $heartbeatAt = is_string($heartbeatRaw) ? Carbon::parse($heartbeatRaw) : null;
        $heartbeatAgeMinutes = $heartbeatAt?->diffInMinutes(now());

        $isHealthy = $failedJobsCount < 20
            && $pendingJobsCount < 1000
            && ($heartbeatAgeMinutes === null || $heartbeatAgeMinutes <= 5);

        return response()->json([
            'status' => $isHealthy ? 'ok' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'queue' => [
                'connection' => config('queue.default'),
                'pending_jobs' => $pendingJobsCount,
                'failed_jobs' => $failedJobsCount,
            ],
            'scheduler' => [
                'heartbeat_at' => $heartbeatAt?->toIso8601String(),
                'heartbeat_age_minutes' => $heartbeatAgeMinutes,
            ],
        ], $isHealthy ? 200 : 503);
    }

    public function readyOps(Request $request): JsonResponse
    {
        $configuredToken = trim((string) config('services.health.token', ''));
        if ($configuredToken === '') {
            abort(404);
        }

        $suppliedToken = trim((string) $request->header('X-Health-Token', ''));
        if ($suppliedToken === '' || ! hash_equals($configuredToken, $suppliedToken)) {
            return response()->json([
                'status' => 'unauthorized',
                'message' => 'Invalid health token.',
            ], 401);
        }

        $dbStarted = microtime(true);
        $databaseReachable = false;
        try {
            DB::connection()->getPdo();
            $databaseReachable = true;
        } catch (Throwable) {
            $databaseReachable = false;
        }
        $dbLatencyMs = (int) round((microtime(true) - $dbStarted) * 1000);

        $jobsStarted = microtime(true);
        $jobsTableReachable = false;
        try {
            $jobsTableReachable = Schema::hasTable('jobs');
            if ($jobsTableReachable) {
                DB::table('jobs')->limit(1)->count();
            }
        } catch (Throwable) {
            $jobsTableReachable = false;
        }
        $jobsLatencyMs = (int) round((microtime(true) - $jobsStarted) * 1000);

        $failedJobsStarted = microtime(true);
        $failedJobsTableReachable = false;
        try {
            $failedJobsTableReachable = Schema::hasTable('failed_jobs');
            if ($failedJobsTableReachable) {
                DB::table('failed_jobs')->limit(1)->count();
            }
        } catch (Throwable) {
            $failedJobsTableReachable = false;
        }
        $failedJobsLatencyMs = (int) round((microtime(true) - $failedJobsStarted) * 1000);

        $isReady = $databaseReachable && $jobsTableReachable && $failedJobsTableReachable;

        return response()->json([
            'status' => $isReady ? 'ready' : 'not_ready',
            'timestamp' => now()->toIso8601String(),
            'checks' => [
                'database' => [
                    'status' => $databaseReachable ? 'ok' : 'unreachable',
                    'latency_ms' => $dbLatencyMs,
                ],
                'jobs_table' => [
                    'status' => $jobsTableReachable ? 'ok' : 'unreachable',
                    'latency_ms' => $jobsLatencyMs,
                ],
                'failed_jobs_table' => [
                    'status' => $failedJobsTableReachable ? 'ok' : 'unreachable',
                    'latency_ms' => $failedJobsLatencyMs,
                ],
            ],
        ], $isReady ? 200 : 503);
    }
}
