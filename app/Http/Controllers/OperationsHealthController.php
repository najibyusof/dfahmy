<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OperationsHealthController extends Controller
{
    public function __invoke(Request $request, TelegramBotService $telegramBotService): View
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
        $telegramConfigured = $telegramBotService->isConfigured();
        $telegramRecipientCount = $telegramBotService->recipientCount();

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
                'status' => $telegramConfigured && $telegramRecipientCount > 0 ? 'healthy' : 'warning',
                'details' => ! $telegramConfigured
                    ? 'Telegram bot token is missing.'
                    : ($telegramRecipientCount > 0
                        ? 'Telegram bot token is configured and ' . $telegramRecipientCount . ' internal user(s) can receive alerts.'
                        : 'Telegram bot token is configured, but no internal user has added a Telegram chat ID on the profile page.'),
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
