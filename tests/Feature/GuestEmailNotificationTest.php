<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Notifications\BookingCancellationEmailNotification;
use App\Notifications\BookingConfirmationEmailNotification;
use App\Notifications\OutstandingBalanceReminderEmailNotification;
use App\Notifications\PaymentReceiptEmailNotification;
use App\Notifications\UpcomingCheckInReminderEmailNotification;
use App\Services\GuestEmailNotificationService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GuestEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_guest_email_notifications_are_queued_for_booking_and_payment_flows(): void
    {
        Notification::fake();

        $service = app(GuestEmailNotificationService::class);

        $guest = Guest::factory()->create(['email' => 'guest@example.com']);
        $room = Room::factory()->create();
        $staff = User::factory()->create();

        $booking = Booking::factory()->create([
            'guest_id' => $guest->id,
            'booking_reference' => 'BK-EMAIL-1001',
            'booking_status' => 'confirmed',
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'total_amount' => 742,
        ]);
        $booking->bookingRoomItems()->delete();
        $booking->bookingRoomItems()->create([
            'room_id' => $room->id,
            'nightly_rate' => 350,
            'adults' => 2,
            'children' => 0,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
        ]);

        $service->sendBookingConfirmation($booking);
        $service->sendBookingCancellation($booking);
        $service->sendOutstandingBalanceReminder($booking, 542.00);
        $service->sendUpcomingCheckInReminder($booking);

        Notification::assertSentTo($guest, BookingConfirmationEmailNotification::class);
        Notification::assertSentTo($guest, BookingCancellationEmailNotification::class);
        Notification::assertSentTo($guest, OutstandingBalanceReminderEmailNotification::class);
        Notification::assertSentTo($guest, UpcomingCheckInReminderEmailNotification::class);

        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'payment_status' => 'paid',
            'amount' => 200,
            'received_by_user_id' => $staff->id,
            'receipt_number' => 'RCPT-EMAIL-1',
        ]);

        $service->sendPaymentReceipt($payment);

        Notification::assertSentTo($guest, PaymentReceiptEmailNotification::class);
    }

    public function test_upcoming_checkin_command_sends_reminders(): void
    {
        Notification::fake();

        $guest = Guest::factory()->create(['email' => 'guest2@example.com']);
        $room = Room::factory()->create();

        $booking = Booking::factory()->create([
            'guest_id' => $guest->id,
            'booking_status' => 'confirmed',
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'total_amount' => 800,
        ]);
        $booking->bookingRoomItems()->delete();
        $booking->bookingRoomItems()->create([
            'room_id' => $room->id,
            'nightly_rate' => 400,
            'adults' => 2,
            'children' => 0,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
        ]);
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'payment_status' => 'paid',
            'amount' => 200,
        ]);

        Artisan::call('bookings:send-upcoming-checkin-reminders');

        Notification::assertSentTo($guest, UpcomingCheckInReminderEmailNotification::class);
        Notification::assertSentTo($guest, OutstandingBalanceReminderEmailNotification::class);
    }

    public function test_no_guest_email_results_in_no_outgoing_mail(): void
    {
        Mail::fake();

        $service = app(GuestEmailNotificationService::class);

        $guest = Guest::factory()->create(['email' => '']);
        $room = Room::factory()->create();

        $booking = Booking::factory()->create([
            'guest_id' => $guest->id,
            'booking_reference' => 'BK-EMAIL-NOADDR',
            'booking_status' => 'confirmed',
        ]);
        $booking->bookingRoomItems()->delete();
        $booking->bookingRoomItems()->create([
            'room_id' => $room->id,
            'nightly_rate' => 200,
            'adults' => 2,
            'children' => 0,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
        ]);

        $service->sendBookingConfirmation($booking);

        Mail::assertNothingOutgoing();
    }
}
