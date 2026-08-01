<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OutstandingBalanceReminderEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Booking $booking, private readonly float $outstandingBalance) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Outstanding Balance Reminder - ' . $this->booking->booking_reference)
            ->view('emails.outstanding-balance-reminder', [
                'booking' => $this->booking->loadMissing('guest', 'bookingRoomItems.room'),
                'outstandingBalance' => round($this->outstandingBalance, 2),
            ]);
    }
}
