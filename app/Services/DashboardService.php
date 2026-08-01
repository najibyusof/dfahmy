<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\HousekeepingTask;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;

class DashboardService
{
    /**
     * Build high-level metrics for the dashboard page.
     *
     * @return array<string, int|string>
     */
    public function forUser(User $user): array
    {
        $today = now()->toDateString();

        $paidTotalsSubQuery = Payment::query()
            ->selectRaw("booking_id, SUM(CASE WHEN payment_status = 'paid' THEN amount ELSE 0 END) as paid_total")
            ->groupBy('booking_id');

        $paymentBaseQuery = Booking::query()
            ->leftJoinSub($paidTotalsSubQuery, 'payment_totals', function ($join): void {
                $join->on('bookings.id', '=', 'payment_totals.booking_id');
            })
            ->whereNotIn('bookings.booking_status', ['cancelled', 'no_show']);

        $unpaidBookings = (clone $paymentBaseQuery)
            ->whereRaw('bookings.total_amount > 0')
            ->whereRaw('COALESCE(payment_totals.paid_total, 0) <= 0')
            ->count();

        $partiallyPaidBookings = (clone $paymentBaseQuery)
            ->whereRaw('COALESCE(payment_totals.paid_total, 0) > 0')
            ->whereRaw('COALESCE(payment_totals.paid_total, 0) < bookings.total_amount')
            ->count();

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

        $recentBookings = Booking::query()
            ->with('guest:id,full_name')
            ->latest('created_at')
            ->limit(6)
            ->get(['id', 'guest_id', 'booking_reference', 'check_in_date', 'check_out_date', 'booking_status', 'created_at']);

        $urgentTasks = HousekeepingTask::query()
            ->with('assignee:id,name')
            ->where('priority', 'urgent')
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest('created_at')
            ->limit(6)
            ->get(['id', 'assigned_to_user_id', 'room_label', 'task_type', 'status', 'due_date', 'priority']);

        return [
            'member_since_year' => $user->created_at->format('Y'),
            'resort_announcements' => 0,
            'upcoming_bookings' => Booking::query()
                ->whereDate('check_in_date', '>', $today)
                ->whereIn('booking_status', ['inquiry', 'pending', 'confirmed'])
                ->count(),
            'todays_arrivals' => Booking::query()->whereDate('check_in_date', $today)->whereNotIn('booking_status', ['cancelled', 'no_show'])->count(),
            'todays_departures' => Booking::query()->whereDate('check_out_date', $today)->whereNotIn('booking_status', ['cancelled', 'no_show'])->count(),
            'current_guests' => Booking::query()->where('booking_status', 'checked_in')->count(),
            'available_rooms' => Room::query()->where('status', 'available')->where('is_active', true)->count(),
            'occupied_rooms' => Room::query()->where('status', 'occupied')->where('is_active', true)->count(),
            'rooms_needing_cleaning' => Room::query()->where('status', 'cleaning')->where('is_active', true)->count(),
            'rooms_under_maintenance' => Room::query()->whereIn('status', ['maintenance', 'out_of_service'])->where('is_active', true)->count(),
            'expected_payments_today' => round($expectedPaymentsToday, 2),
            'outstanding_balances' => round($outstandingBalances, 2),
            'pending_tasks' => HousekeepingTask::query()->whereIn('status', ['pending', 'in_progress'])->count(),
            'unpaid_bookings' => $unpaidBookings,
            'partially_paid_bookings' => $partiallyPaidBookings,
            'recent_bookings' => $recentBookings,
            'urgent_tasks' => $urgentTasks,
        ];
    }
}
