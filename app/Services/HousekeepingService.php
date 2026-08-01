<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingRoomItem;
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use App\Notifications\HousekeepingTaskAssignedNotification;
use Illuminate\Support\Facades\DB;

class HousekeepingService
{
    public function __construct(
        private readonly InAppNotificationService $notificationService,
        private readonly TelegramAlertService $telegramAlertService
    ) {}

    public function createCheckoutTasksForBooking(Booking $booking, User $actor): void
    {
        DB::transaction(function () use ($booking, $actor): void {
            $booking->loadMissing('bookingRoomItems.room');

            $defaultAssignee = User::query()
                ->whereHas('roles.permissions', function ($query): void {
                    $query->where('name', 'housekeeping.assigned.view');
                })
                ->orderBy('id')
                ->first();

            $assigneeId = $defaultAssignee?->id ?? $actor->id;

            $uniqueRoomItems = $booking->bookingRoomItems
                ->unique('room_id')
                ->values();

            foreach ($uniqueRoomItems as $item) {
                $room = $item->room;
                if ($room === null) {
                    continue;
                }

                $room->update(['status' => 'cleaning']);

                $task = HousekeepingTask::query()->create([
                    'assigned_to_user_id' => $assigneeId,
                    'room_id' => $room->id,
                    'booking_id' => $booking->id,
                    'room_label' => $room->code,
                    'task_type' => 'checkout_cleaning',
                    'priority' => 'high',
                    'due_date' => now()->toDateString(),
                    'status' => 'pending',
                    'notes' => 'Auto-created at checkout for booking ' . $booking->booking_reference,
                    'checklist_notes' => null,
                    'completed_at' => null,
                ]);

                if ($task->assignee !== null) {
                    $task->assignee->notify(new HousekeepingTaskAssignedNotification($task));
                }
            }
        });
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function createTask(array $validated): HousekeepingTask
    {
        return DB::transaction(function () use ($validated): HousekeepingTask {
            $task = HousekeepingTask::query()->create($this->normalizeTaskPayload($validated));
            $task->refresh();

            if ($task->assignee !== null) {
                $task->assignee->notify(new HousekeepingTaskAssignedNotification($task));
            }

            if ($task->task_type === 'maintenance') {
                $this->notificationService->notifyMaintenanceRequestCreated($task);
                $this->notificationService->notifyMaintenanceRequestAssigned($task);

                if ($task->priority === 'urgent') {
                    $this->telegramAlertService->urgentMaintenanceRequest($task);
                }
            } elseif ($task->priority === 'urgent') {
                $this->telegramAlertService->urgentHousekeepingTask($task);
            }

            return $task;
        });
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function updateTask(HousekeepingTask $task, array $validated): void
    {
        DB::transaction(function () use ($task, $validated): void {
            $oldAssigneeId = (int) $task->assigned_to_user_id;
            $oldStatus = (string) $task->status;
            $task->update($this->normalizeTaskPayload($validated));
            $task->refresh();

            if ((int) $task->assigned_to_user_id !== $oldAssigneeId && $task->assignee !== null) {
                $task->assignee->notify(new HousekeepingTaskAssignedNotification($task));

                if ($task->task_type === 'maintenance') {
                    $this->notificationService->notifyMaintenanceRequestAssigned($task);
                }
            }

            if ($task->priority === 'urgent') {
                if ($task->task_type === 'maintenance') {
                    $this->telegramAlertService->urgentMaintenanceRequest($task);
                } else {
                    $this->telegramAlertService->urgentHousekeepingTask($task);
                }
            }

            if ($task->task_type === 'maintenance' && $oldStatus !== 'completed' && $task->status === 'completed') {
                $this->notificationService->notifyMaintenanceRequestResolved($task);
            }

            $this->applyPostCompletionRoomStatusRules($task);
        });
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function updateAssignedTask(HousekeepingTask $task, array $validated): void
    {
        DB::transaction(function () use ($task, $validated): void {
            $oldStatus = (string) $task->status;
            $status = (string) $validated['status'];
            $payload = [
                'status' => $status,
                'checklist_notes' => $validated['checklist_notes'] ?? $task->checklist_notes,
            ];

            if ($status === 'completed' && $task->completed_at === null) {
                $payload['completed_at'] = now();
            }

            $task->update($payload);
            $task->refresh();

            if ($task->task_type === 'maintenance' && $oldStatus !== 'completed' && $task->status === 'completed') {
                $this->notificationService->notifyMaintenanceRequestResolved($task);
            }

            $this->applyPostCompletionRoomStatusRules($task);
        });
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function normalizeTaskPayload(array $validated): array
    {
        $roomId = isset($validated['room_id']) ? (int) $validated['room_id'] : null;
        $room = $roomId !== null ? Room::query()->find($roomId) : null;
        $status = (string) ($validated['status'] ?? 'pending');

        return [
            'room_id' => $room?->id,
            'booking_id' => isset($validated['booking_id']) && $validated['booking_id'] !== '' ? (int) $validated['booking_id'] : null,
            'assigned_to_user_id' => (int) $validated['assigned_to_user_id'],
            'room_label' => $room?->code ?? (string) ($validated['room_label'] ?? 'N/A'),
            'task_type' => (string) $validated['task_type'],
            'priority' => (string) $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
            'checklist_notes' => $validated['checklist_notes'] ?? null,
            'completed_at' => $status === 'completed' ? ($validated['completed_at'] ?? now()) : null,
        ];
    }

    private function applyPostCompletionRoomStatusRules(HousekeepingTask $task): void
    {
        if ($task->task_type !== 'checkout_cleaning' || $task->status !== 'completed' || $task->room_id === null) {
            return;
        }

        $room = Room::query()->find($task->room_id);
        if ($room === null) {
            return;
        }

        if (in_array($room->status, ['maintenance', 'out_of_service'], true)) {
            return;
        }

        $hasOpenMaintenanceTask = HousekeepingTask::query()
            ->where('room_id', $room->id)
            ->where('task_type', 'maintenance')
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();

        if ($hasOpenMaintenanceTask) {
            $oldStatus = (string) $room->status;
            $room->update(['status' => 'maintenance']);
            $room->refresh();
            $this->notificationService->notifyRoomStatusChangedToMaintenanceOrOutOfService($room, $oldStatus);
            return;
        }

        $hasIncomingReservation = BookingRoomItem::query()
            ->where('room_id', $room->id)
            ->whereDate('check_in_date', '>=', now()->toDateString())
            ->whereHas('booking', function ($query): void {
                $query->whereIn('booking_status', Booking::BLOCKING_STATUSES);
            })
            ->exists();

        if ($hasIncomingReservation) {
            $room->update(['status' => 'reserved']);
            return;
        }

        $room->update(['status' => 'available']);
    }
}
