<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingRoomItem;
use App\Models\Building;
use App\Models\HousekeepingTask;
use App\Models\Payment;
use App\Models\Room;
use Carbon\Carbon;

class ReportService
{
    /**
     * @return array<string, mixed>
     */
    public function generate(string $from, string $to): array
    {
        return [
            'occupancy_by_building' => $this->occupancyByBuilding($from, $to),
            'booking_source_summary' => $this->bookingSourceSummary($from, $to),
            'revenue_summary' => $this->revenueSummary($from, $to),
            'payment_method_summary' => $this->paymentMethodSummary($from, $to),
            'outstanding_balance_report' => $this->outstandingBalanceReport($from, $to),
            'housekeeping_report' => $this->housekeepingReport($from, $to),
            'maintenance_report' => $this->maintenanceReport($from, $to),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function occupancyByBuilding(string $from, string $to): array
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();
        $rangeDays = max(1, $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1);

        $roomCountByBuilding = Building::query()
            ->withCount(['rooms' => function ($query): void {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        $bookedNightsByBuilding = [];
        $items = BookingRoomItem::query()
            ->with(['room:id,building_id', 'booking:id,booking_status'])
            ->whereDate('check_in_date', '<=', $to)
            ->whereDate('check_out_date', '>=', $from)
            ->get();

        foreach ($items as $item) {
            if ($item->room === null || $item->booking === null) {
                continue;
            }

            if (! in_array((string) $item->booking->booking_status, ['confirmed', 'checked_in', 'checked_out'], true)) {
                continue;
            }

            $itemStart = Carbon::parse($item->check_in_date)->startOfDay();
            $itemEnd = Carbon::parse($item->check_out_date)->startOfDay();

            $overlapStart = $itemStart->greaterThan($start) ? $itemStart : $start->copy();
            $overlapEnd = $itemEnd->lessThan($end->copy()->addDay()) ? $itemEnd : $end->copy()->addDay();

            $nights = $overlapStart->diffInDays($overlapEnd, false);
            if ($nights <= 0) {
                continue;
            }

            $buildingId = (int) $item->room->building_id;
            $bookedNightsByBuilding[$buildingId] = ($bookedNightsByBuilding[$buildingId] ?? 0) + $nights;
        }

        $rows = [];
        foreach ($roomCountByBuilding as $building) {
            $rooms = (int) $building->rooms_count;
            $bookedNights = (int) ($bookedNightsByBuilding[(int) $building->id] ?? 0);
            $totalRoomNights = $rooms * $rangeDays;
            $occupancyRate = $totalRoomNights > 0 ? round(($bookedNights / $totalRoomNights) * 100, 2) : 0.0;

            $rows[] = [
                'building' => $building->name,
                'rooms' => $rooms,
                'booked_nights' => $bookedNights,
                'total_room_nights' => $totalRoomNights,
                'occupancy_rate_percent' => $occupancyRate,
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function bookingSourceSummary(string $from, string $to): array
    {
        return Booking::query()
            ->selectRaw('booking_source, COUNT(*) as total_bookings, COALESCE(SUM(total_amount),0) as total_amount')
            ->whereDate('check_in_date', '>=', $from)
            ->whereDate('check_in_date', '<=', $to)
            ->groupBy('booking_source')
            ->orderByDesc('total_bookings')
            ->get()
            ->map(static fn(Booking $row): array => [
                'booking_source' => $row->booking_source,
                'total_bookings' => (int) $row->total_bookings,
                'total_amount' => round((float) $row->total_amount, 2),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function revenueSummary(string $from, string $to): array
    {
        $paid = (float) Payment::query()
            ->where('payment_status', 'paid')
            ->whereDate('payment_date', '>=', $from)
            ->whereDate('payment_date', '<=', $to)
            ->sum('amount');

        $refunded = (float) Payment::query()
            ->where('payment_status', 'refunded')
            ->whereDate('payment_date', '>=', $from)
            ->whereDate('payment_date', '<=', $to)
            ->sum('amount');

        $pending = (float) Payment::query()
            ->where('payment_status', 'pending')
            ->whereDate('payment_date', '>=', $from)
            ->whereDate('payment_date', '<=', $to)
            ->sum('amount');

        return [
            'paid_amount' => round($paid, 2),
            'refunded_amount' => round($refunded, 2),
            'pending_amount' => round($pending, 2),
            'net_collected' => round($paid - $refunded, 2),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function paymentMethodSummary(string $from, string $to): array
    {
        return Payment::query()
            ->selectRaw('payment_method, COUNT(*) as total_transactions, COALESCE(SUM(amount),0) as total_amount')
            ->where('payment_status', 'paid')
            ->whereDate('payment_date', '>=', $from)
            ->whereDate('payment_date', '<=', $to)
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get()
            ->map(static fn(Payment $row): array => [
                'payment_method' => $row->payment_method,
                'total_transactions' => (int) $row->total_transactions,
                'total_amount' => round((float) $row->total_amount, 2),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function outstandingBalanceReport(string $from, string $to): array
    {
        $bookings = Booking::query()
            ->with(['guest:id,full_name', 'payments:id,booking_id,amount,payment_status'])
            ->whereDate('check_in_date', '>=', $from)
            ->whereDate('check_in_date', '<=', $to)
            ->whereNotIn('booking_status', ['cancelled', 'no_show'])
            ->orderBy('check_in_date')
            ->get();

        $rows = [];
        foreach ($bookings as $booking) {
            $outstanding = $booking->outstandingBalanceAmount();
            if ($outstanding <= 0.0) {
                continue;
            }

            $rows[] = [
                'booking_reference' => $booking->booking_reference,
                'guest' => $booking->guest?->full_name,
                'check_in_date' => $booking->check_in_date?->format('Y-m-d'),
                'booking_status' => $booking->booking_status,
                'total_amount' => round((float) $booking->total_amount, 2),
                'outstanding_balance' => round($outstanding, 2),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    public function housekeepingReport(string $from, string $to): array
    {
        $query = HousekeepingTask::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        $tasks = $query->get();

        return [
            'total_tasks' => $tasks->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'cancelled' => $tasks->where('status', 'cancelled')->count(),
            'urgent_open_tasks' => $tasks
                ->where('priority', 'urgent')
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'overdue_tasks' => $tasks
                ->whereIn('status', ['pending', 'in_progress'])
                ->filter(
                    static fn(HousekeepingTask $task): bool =>
                    $task->due_date !== null && $task->due_date->isBefore(now()->startOfDay())
                )
                ->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function maintenanceReport(string $from, string $to): array
    {
        $tasks = HousekeepingTask::query()
            ->where('task_type', 'maintenance')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->get();

        return [
            'total_requests' => $tasks->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'cancelled' => $tasks->where('status', 'cancelled')->count(),
            'urgent_open_requests' => $tasks
                ->where('priority', 'urgent')
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    public function dashboardMetrics(): array
    {
        $today = now()->toDateString();

        $expectedPaymentsToday = (float) Booking::query()
            ->with(['payments:id,booking_id,amount,payment_status'])
            ->whereDate('check_in_date', $today)
            ->whereNotIn('booking_status', ['cancelled', 'no_show'])
            ->get()
            ->sum(static fn(Booking $booking): float => $booking->outstandingBalanceAmount());

        $outstandingBalances = (float) Booking::query()
            ->with(['payments:id,booking_id,amount,payment_status'])
            ->whereNotIn('booking_status', ['cancelled', 'no_show'])
            ->get()
            ->sum(static fn(Booking $booking): float => $booking->outstandingBalanceAmount());

        return [
            'todays_arrivals' => Booking::query()->whereDate('check_in_date', $today)->whereNotIn('booking_status', ['cancelled', 'no_show'])->count(),
            'todays_departures' => Booking::query()->whereDate('check_out_date', $today)->whereNotIn('booking_status', ['cancelled', 'no_show'])->count(),
            'current_guests' => Booking::query()->where('booking_status', 'checked_in')->count(),
            'available_rooms' => Room::query()->where('status', 'available')->where('is_active', true)->count(),
            'occupied_rooms' => Room::query()->where('status', 'occupied')->where('is_active', true)->count(),
            'rooms_needing_cleaning' => Room::query()->where('status', 'cleaning')->where('is_active', true)->count(),
            'rooms_under_maintenance' => Room::query()->whereIn('status', ['maintenance', 'out_of_service'])->where('is_active', true)->count(),
            'expected_payments_today' => round($expectedPaymentsToday, 2),
            'outstanding_balances' => round($outstandingBalances, 2),
        ];
    }
}
