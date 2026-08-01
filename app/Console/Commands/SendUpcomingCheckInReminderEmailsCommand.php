<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\GuestEmailNotificationService;
use Illuminate\Console\Command;

class SendUpcomingCheckInReminderEmailsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'bookings:send-upcoming-checkin-reminders';

    /**
     * @var string
     */
    protected $description = 'Send queued guest email reminders for upcoming check-ins and outstanding balances';

    public function handle(GuestEmailNotificationService $guestEmailNotificationService): int
    {
        $targetDate = now()->addDay()->toDateString();

        $bookings = Booking::query()
            ->with(['guest', 'payments', 'bookingRoomItems.room'])
            ->whereDate('check_in_date', $targetDate)
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->get();

        foreach ($bookings as $booking) {
            $guestEmailNotificationService->sendUpcomingCheckInReminder($booking);

            $outstanding = $booking->outstandingBalanceAmount();
            if ($outstanding > 0.0) {
                $guestEmailNotificationService->sendOutstandingBalanceReminder($booking, $outstanding);
            }
        }

        $this->info('Upcoming check-in reminders processed: ' . $bookings->count());

        return self::SUCCESS;
    }
}
