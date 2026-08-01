<?php

namespace App\Notifications;

use App\Models\HousekeepingTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class HousekeepingTaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly HousekeepingTask $task) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Housekeeping Task Assigned',
            'message' => 'A housekeeping task has been assigned for room ' . $this->task->room_label . '.',
            'type' => 'housekeeping_task_assigned',
            'link' => route('housekeeping.tasks.index'),
            'created_time' => now()->toIso8601String(),
            'read_status' => false,
            'meta' => [
                'task_id' => $this->task->id,
                'room_label' => $this->task->room_label,
                'task_type' => $this->task->task_type,
                'priority' => $this->task->priority,
                'due_date' => $this->task->due_date?->format('Y-m-d'),
            ],
        ];
    }
}
