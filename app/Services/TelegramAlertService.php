<?php

namespace App\Services;

use App\Jobs\SendTelegramMessageJob;
use App\Models\Booking;
use App\Models\HousekeepingTask;
use App\Models\OperationAlertSetting;

class TelegramAlertService
{
    public function sendTestMessage(string $actorName): void
    {
        SendTelegramMessageJob::dispatch(
            '*Telegram Test Alert*' . "\n"
                . 'Triggered by: ' . $actorName . "\n"
                . 'Time: ' . now()->format('Y-m-d H:i:s')
        );
    }

    public function newBooking(Booking $booking): void
    {
        $this->dispatchIfEnabled(
            'telegram_new_booking',
            "*New Booking*\\nRef: {$booking->booking_reference}\\nGuest: {$booking->guest?->full_name}\\nCheck-in: {$booking->check_in_date?->format('Y-m-d')}\\nCheck-out: {$booking->check_out_date?->format('Y-m-d')}"
        );
    }

    public function bookingCancellation(Booking $booking): void
    {
        $this->dispatchIfEnabled(
            'telegram_booking_cancellation',
            "*Booking Cancelled*\\nRef: {$booking->booking_reference}\\nGuest: {$booking->guest?->full_name}"
        );
    }

    public function checkIn(Booking $booking): void
    {
        $this->dispatchIfEnabled(
            'telegram_check_in',
            "*Guest Check-In*\\nRef: {$booking->booking_reference}\\nGuest: {$booking->guest?->full_name}"
        );
    }

    public function checkOut(Booking $booking): void
    {
        $this->dispatchIfEnabled(
            'telegram_check_out',
            "*Guest Check-Out*\\nRef: {$booking->booking_reference}\\nGuest: {$booking->guest?->full_name}"
        );
    }

    public function overduePaymentOrOutstandingBalance(Booking $booking, float $amount): void
    {
        $this->dispatchIfEnabled(
            'telegram_overdue_payment_outstanding_balance',
            "*Outstanding Balance Alert*\\nRef: {$booking->booking_reference}\\nGuest: {$booking->guest?->full_name}\\nOutstanding: RM " . number_format($amount, 2)
        );
    }

    public function urgentHousekeepingTask(HousekeepingTask $task): void
    {
        $this->dispatchIfEnabled(
            'telegram_urgent_housekeeping_task',
            "*Urgent Housekeeping Task*\\nRoom: {$task->room_label}\\nType: " . str_replace('_', ' ', (string) $task->task_type) . "\\nDue: {$task->due_date?->format('Y-m-d')}"
        );
    }

    public function urgentMaintenanceRequest(HousekeepingTask $task): void
    {
        $this->dispatchIfEnabled(
            'telegram_urgent_maintenance_request',
            "*Urgent Maintenance Request*\\nRoom: {$task->room_label}\\nStatus: " . str_replace('_', ' ', (string) $task->status) . "\\nDue: {$task->due_date?->format('Y-m-d')}"
        );
    }

    public function isEnabled(string $key): bool
    {
        if (! in_array($key, OperationAlertSetting::TELEGRAM_KEYS, true)) {
            return false;
        }

        $setting = OperationAlertSetting::query()->where('key', $key)->first();

        return $setting?->enabled ?? true;
    }

    public function allToggleStates(): array
    {
        $rows = OperationAlertSetting::query()
            ->whereIn('key', OperationAlertSetting::TELEGRAM_KEYS)
            ->pluck('enabled', 'key');

        $states = [];
        foreach (OperationAlertSetting::TELEGRAM_KEYS as $key) {
            $states[$key] = (bool) ($rows[$key] ?? true);
        }

        return $states;
    }

    /**
     * @param array<string, bool> $toggles
     */
    public function saveToggleStates(array $toggles): void
    {
        foreach (OperationAlertSetting::TELEGRAM_KEYS as $key) {
            OperationAlertSetting::query()->updateOrCreate(
                ['key' => $key],
                ['enabled' => (bool) ($toggles[$key] ?? false)]
            );
        }
    }

    private function dispatchIfEnabled(string $key, string $message): void
    {
        if (! $this->isEnabled($key)) {
            return;
        }

        SendTelegramMessageJob::dispatch($message);
    }
}
