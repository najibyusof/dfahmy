<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\HousekeepingTask;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Notifications\InAppSystemNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class InAppNotificationService
{
    public function notifyNewBooking(Booking $booking): void
    {
        $this->notifyUsersByPermission('bookings.manage', [
            'title' => 'New Booking',
            'message' => 'A new booking ' . $booking->booking_reference . ' has been created.',
            'type' => 'booking_new',
            'link' => route('bookings.show', $booking),
            'meta' => ['booking_id' => $booking->id],
        ]);
    }

    public function notifyBookingConfirmed(Booking $booking): void
    {
        $this->notifyUsersByPermission('bookings.manage', [
            'title' => 'Booking Confirmed',
            'message' => 'Booking ' . $booking->booking_reference . ' is now confirmed.',
            'type' => 'booking_confirmed',
            'link' => route('bookings.show', $booking),
            'meta' => ['booking_id' => $booking->id],
        ]);
    }

    public function notifyBookingCancelled(Booking $booking): void
    {
        $this->notifyUsersByPermission('bookings.manage', [
            'title' => 'Booking Cancelled',
            'message' => 'Booking ' . $booking->booking_reference . ' has been cancelled.',
            'type' => 'booking_cancelled',
            'link' => route('bookings.show', $booking),
            'meta' => ['booking_id' => $booking->id],
        ]);
    }

    public function notifyCheckIn(Booking $booking): void
    {
        $this->notifyUsersByPermission('bookings.manage', [
            'title' => 'Guest Checked In',
            'message' => 'Booking ' . $booking->booking_reference . ' has checked in.',
            'type' => 'booking_check_in',
            'link' => route('bookings.show', $booking),
            'meta' => ['booking_id' => $booking->id],
        ]);
    }

    public function notifyCheckOut(Booking $booking): void
    {
        $this->notifyUsersByPermission('bookings.manage', [
            'title' => 'Guest Checked Out',
            'message' => 'Booking ' . $booking->booking_reference . ' has checked out.',
            'type' => 'booking_check_out',
            'link' => route('bookings.show', $booking),
            'meta' => ['booking_id' => $booking->id],
        ]);
    }

    public function notifyOutstandingBalanceBeforeCheckIn(Booking $booking, float $outstandingBalance): void
    {
        $this->notifyUsersByPermission('bookings.manage', [
            'title' => 'Outstanding Balance Before Check-In',
            'message' => 'Booking ' . $booking->booking_reference . ' has outstanding balance RM ' . number_format($outstandingBalance, 2) . ' before check-in.',
            'type' => 'booking_outstanding_before_check_in',
            'link' => route('bookings.show', $booking),
            'meta' => [
                'booking_id' => $booking->id,
                'outstanding_balance' => round($outstandingBalance, 2),
            ],
        ]);
    }

    public function notifyNewPayment(Payment $payment): void
    {
        $payment->loadMissing('booking:id,booking_reference');

        $this->notifyUsersByPermission('payments.manage', [
            'title' => 'New Payment Received',
            'message' => 'Payment ' . $payment->receipt_number . ' recorded for booking ' . ($payment->booking?->booking_reference ?? '#' . $payment->booking_id) . '.',
            'type' => 'payment_new',
            'link' => route('payments.show', $payment),
            'meta' => [
                'payment_id' => $payment->id,
                'booking_id' => $payment->booking_id,
            ],
        ]);
    }

    public function notifyHousekeepingTaskOverdue(HousekeepingTask $task): void
    {
        $task->loadMissing('assignee:id');

        $userIds = [];

        if ($task->assignee !== null && $task->assignee->can('housekeeping.assigned.view')) {
            $userIds[] = (int) $task->assignee->id;
        }

        foreach ($this->usersWithPermission('housekeeping.manage') as $manager) {
            $userIds[] = (int) $manager->id;
        }

        $this->notifyUserIds(array_values(array_unique($userIds)), [
            'title' => 'Housekeeping Task Overdue',
            'message' => 'Task for room ' . $task->room_label . ' is overdue.',
            'type' => 'housekeeping_task_overdue',
            'link' => route('housekeeping.tasks.index'),
            'meta' => [
                'housekeeping_task_id' => $task->id,
                'room_id' => $task->room_id,
            ],
        ]);
    }

    public function notifyMaintenanceRequestCreated(HousekeepingTask $task): void
    {
        $this->notifyUsersByPermission('maintenance.manage', [
            'title' => 'Maintenance Request Created',
            'message' => 'Maintenance request for room ' . $task->room_label . ' has been created.',
            'type' => 'maintenance_request_created',
            'link' => route('modules.maintenance.index'),
            'meta' => [
                'housekeeping_task_id' => $task->id,
                'room_id' => $task->room_id,
            ],
        ]);
    }

    public function notifyMaintenanceRequestAssigned(HousekeepingTask $task): void
    {
        $this->notifyUsersByPermission('maintenance.manage', [
            'title' => 'Maintenance Request Assigned',
            'message' => 'Maintenance request for room ' . $task->room_label . ' has been assigned.',
            'type' => 'maintenance_request_assigned',
            'link' => route('modules.maintenance.index'),
            'meta' => [
                'housekeeping_task_id' => $task->id,
                'room_id' => $task->room_id,
                'assignee_id' => $task->assigned_to_user_id,
            ],
        ]);
    }

    public function notifyMaintenanceRequestResolved(HousekeepingTask $task): void
    {
        $this->notifyUsersByPermission('maintenance.manage', [
            'title' => 'Maintenance Request Resolved',
            'message' => 'Maintenance request for room ' . $task->room_label . ' has been resolved.',
            'type' => 'maintenance_request_resolved',
            'link' => route('modules.maintenance.index'),
            'meta' => [
                'housekeeping_task_id' => $task->id,
                'room_id' => $task->room_id,
            ],
        ]);
    }

    public function notifyRoomStatusChangedToMaintenanceOrOutOfService(Room $room, string $oldStatus): void
    {
        if (! in_array($room->status, ['maintenance', 'out_of_service'], true)) {
            return;
        }

        if ($oldStatus === $room->status) {
            return;
        }

        $this->notifyUsersByPermission('maintenance.manage', [
            'title' => 'Room Status Updated',
            'message' => 'Room ' . $room->code . ' changed from ' . str_replace('_', ' ', $oldStatus) . ' to ' . str_replace('_', ' ', $room->status) . '.',
            'type' => 'room_status_maintenance_or_out_of_service',
            'link' => route('rooms.edit', $room),
            'meta' => [
                'room_id' => $room->id,
                'old_status' => $oldStatus,
                'new_status' => $room->status,
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function notifyUsersByPermission(string $permission, array $payload): void
    {
        $users = $this->usersWithPermission($permission);

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new InAppSystemNotification($this->normalizePayload($payload)));
    }

    /**
     * @param array<int> $userIds
     * @param array<string, mixed> $payload
     */
    private function notifyUserIds(array $userIds, array $payload): void
    {
        if ($userIds === []) {
            return;
        }

        $users = User::query()->whereIn('id', $userIds)->get();
        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new InAppSystemNotification($this->normalizePayload($payload)));
    }

    /**
     * @return Collection<int, User>
     */
    private function usersWithPermission(string $permission): Collection
    {
        return User::query()
            ->whereHas('roles.permissions', function ($query) use ($permission): void {
                $query->where('name', $permission);
            })
            ->get();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        $payload['created_time'] = $payload['created_time'] ?? now()->toIso8601String();

        return $payload;
    }
}
