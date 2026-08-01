<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\TelegramAlertService;
use Illuminate\Console\Command;

class SendOutstandingBalanceTelegramAlertsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'bookings:send-outstanding-balance-telegram-alerts';

    /**
     * @var string
     */
    protected $description = 'Send Telegram alerts for overdue or outstanding booking balances';

    public function handle(TelegramAlertService $telegramAlertService): int
    {
        $bookings = Booking::query()
            ->with(['guest', 'payments'])
            ->whereNotIn('booking_status', ['cancelled', 'no_show', 'checked_out'])
            ->whereDate('check_in_date', '<=', now()->toDateString())
            ->get();

        $sent = 0;
        foreach ($bookings as $booking) {
            $outstanding = $booking->outstandingBalanceAmount();
            if ($outstanding <= 0.0) {
                continue;
            }

            $telegramAlertService->overduePaymentOrOutstandingBalance($booking, $outstanding);
            $sent++;
        }

        $this->info('Outstanding balance Telegram alerts queued: ' . $sent);

        return self::SUCCESS;
    }
}
