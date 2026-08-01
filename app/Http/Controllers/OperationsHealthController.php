<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OperationsHealthController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $failedJobsCount = 0;
        $failedJobsLast24Hours = 0;
        if (Schema::hasTable('failed_jobs')) {
            $failedJobsCount = (int) DB::table('failed_jobs')->count();
            $failedJobsLast24Hours = (int) DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->count();
        }

        $pendingJobsCount = 0;
        $oldestPendingAgeMinutes = null;
        if (Schema::hasTable('jobs')) {
            $pendingJobsCount = (int) DB::table('jobs')->count();
            $oldestCreatedAt = DB::table('jobs')->orderBy('created_at')->value('created_at');

            if ($oldestCreatedAt !== null) {
                $oldestPendingAgeMinutes = (int) floor(max(0, now()->diffInSeconds(Carbon::createFromTimestamp((int) $oldestCreatedAt), false)) / 60);
            }
        }

        $heartbeatRaw = Cache::get('system.scheduler_heartbeat_at');
        $heartbeatAt = is_string($heartbeatRaw) ? Carbon::parse($heartbeatRaw) : null;
        $heartbeatAgeMinutes = $heartbeatAt?->diffInMinutes(now());

        $queueConnection = (string) config('queue.default');
        $queueFailedDriver = (string) config('queue.failed.driver');
        $mailer = (string) config('mail.default');
        $telegramConfigured = trim((string) config('services.telegram.bot_token')) !== ''
            && trim((string) config('services.telegram.chat_id')) !== '';

        $checks = [
            [
                'name' => 'Scheduler heartbeat',
                'status' => $heartbeatAgeMinutes !== null && $heartbeatAgeMinutes <= 2 ? 'healthy' : 'warning',
                'details' => $heartbeatAt === null
                    ? 'No heartbeat recorded yet.'
                    : 'Last heartbeat ' . $heartbeatAt->diffForHumans() . '.',
            ],
            [
                'name' => 'Failed jobs backlog',
                'status' => $failedJobsCount === 0 ? 'healthy' : ($failedJobsCount <= 10 ? 'warning' : 'critical'),
                'details' => $failedJobsCount . ' failed job(s), ' . $failedJobsLast24Hours . ' in last 24 hours.',
            ],
            [
                'name' => 'Pending jobs backlog',
                'status' => $pendingJobsCount <= 200 ? 'healthy' : 'warning',
                'details' => $oldestPendingAgeMinutes === null
                    ? $pendingJobsCount . ' pending job(s).'
                    : $pendingJobsCount . ' pending job(s), oldest pending for ' . $oldestPendingAgeMinutes . ' minute(s).',
            ],
            [
                'name' => 'Telegram integration',
                'status' => $telegramConfigured ? 'healthy' : 'warning',
                'details' => $telegramConfigured
                    ? 'Telegram bot token and chat id are configured.'
                    : 'Telegram bot token or chat id is missing.',
            ],
            [
                'name' => 'Mail delivery mode',
                'status' => $mailer === 'log' ? 'warning' : 'healthy',
                'details' => 'Current mailer: ' . $mailer . '.',
            ],
        ];

        return view('admin.operations-health.index', [
            'failedJobsCount' => $failedJobsCount,
            'failedJobsLast24Hours' => $failedJobsLast24Hours,
            'pendingJobsCount' => $pendingJobsCount,
            'oldestPendingAgeMinutes' => $oldestPendingAgeMinutes,
            'heartbeatAt' => $heartbeatAt,
            'heartbeatAgeMinutes' => $heartbeatAgeMinutes,
            'queueConnection' => $queueConnection,
            'queueFailedDriver' => $queueFailedDriver,
            'mailer' => $mailer,
            'telegramConfigured' => $telegramConfigured,
            'checks' => $checks,
        ]);
    }
}
