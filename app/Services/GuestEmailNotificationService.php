<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Notifications\BookingCancellationEmailNotification;
use App\Notifications\BookingConfirmationEmailNotification;
use App\Notifications\OutstandingBalanceReminderEmailNotification;
use App\Notifications\PaymentReceiptEmailNotification;
use App\Notifications\UpcomingCheckInReminderEmailNotification;

class GuestEmailNotificationService
{
    public function sendBookingConfirmation(Booking $booking): void
    {
        $booking->loadMissing('guest');

        if ($booking->guest === null) {
            return;
        }

        $this->notifyGuest($booking->guest, new BookingConfirmationEmailNotification($booking));
    }

    public function sendBookingCancellation(Booking $booking): void
    {
        $booking->loadMissing('guest');

        if ($booking->guest === null) {
            return;
        }

        $this->notifyGuest($booking->guest, new BookingCancellationEmailNotification($booking));
    }

    public function sendUpcomingCheckInReminder(Booking $booking): void
    {
        $booking->loadMissing('guest');

        if ($booking->guest === null) {
            return;
        }

        $this->notifyGuest($booking->guest, new UpcomingCheckInReminderEmailNotification($booking));
    }

    public function sendPaymentReceipt(Payment $payment): void
    {
        $payment->loadMissing('booking.guest');

        if ($payment->payment_status !== 'paid') {
            return;
        }

        if ($payment->booking === null || $payment->booking->guest === null) {
            return;
        }

        $this->notifyGuest($payment->booking->guest, new PaymentReceiptEmailNotification($payment));
    }

    public function sendOutstandingBalanceReminder(Booking $booking, float $outstandingBalance): void
    {
        $booking->loadMissing('guest');

        if ($booking->guest === null || $outstandingBalance <= 0.0) {
            return;
        }

        $this->notifyGuest($booking->guest, new OutstandingBalanceReminderEmailNotification($booking, $outstandingBalance));
    }

    private function notifyGuest(Guest $guest, object $notification): void
    {
        $email = trim((string) $guest->email);
        if ($email === '') {
            return;
        }

        $guest->notify($notification);
    }
}
