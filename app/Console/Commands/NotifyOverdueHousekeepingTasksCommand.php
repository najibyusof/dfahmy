<?php

namespace App\Console\Commands;

use App\Models\HousekeepingTask;
use App\Services\InAppNotificationService;
use Illuminate\Console\Command;

class NotifyOverdueHousekeepingTasksCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'housekeeping:notify-overdue';

    /**
     * @var string
     */
    protected $description = 'Send in-app notifications for overdue housekeeping tasks';

    public function handle(InAppNotificationService $notificationService): int
    {
        $tasks = HousekeepingTask::query()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        foreach ($tasks as $task) {
            $notificationService->notifyHousekeepingTaskOverdue($task);
        }

        $this->info('Overdue housekeeping notifications sent: ' . $tasks->count());

        return self::SUCCESS;
    }
}
