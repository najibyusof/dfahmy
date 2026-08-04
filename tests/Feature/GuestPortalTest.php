<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_portal_shows_availability_and_only_the_users_booking_history(): void
    {
        $user = User::factory()->create();
        $guest = Guest::factory()->create(['user_id' => $user->id]);
        $otherGuest = Guest::factory()->create();
        Room::factory()->create(['name' => 'Garden Room', 'code' => 'GR-01', 'is_active' => true]);

        Booking::factory()->create([
            'guest_id' => $guest->id,
            'booking_reference' => 'MINE-BOOKING-001',
            'check_in_date' => now()->subDays(10)->toDateString(),
            'check_out_date' => now()->subDays(8)->toDateString(),
        ]);
        Booking::factory()->create([
            'guest_id' => $otherGuest->id,
            'booking_reference' => 'OTHER-BOOKING-001',
            'check_in_date' => now()->subDays(10)->toDateString(),
            'check_out_date' => now()->subDays(8)->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('guest.portal'))
            ->assertOk()
            ->assertSee('Available dates')
            ->assertSee('Garden Room')
            ->assertSee('MINE-BOOKING-001')
            ->assertDontSee('OTHER-BOOKING-001')
            ->assertDontSee('>Dashboard<', false)
            ->assertSee('Book a Stay')
            ->assertSee('My Bookings');
    }

    public function test_guest_can_submit_an_available_room_booking_with_server_calculated_price(): void
    {
        $user = User::factory()->create(['name' => 'Portal Guest', 'email' => 'portal@example.test']);
        $guest = Guest::factory()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'phone_number' => null,
            'identification_number' => null,
        ]);
        $room = Room::factory()->create([
            'base_nightly_rate' => 250,
            'maximum_guests' => 4,
            'is_active' => true,
        ]);
        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(8)->toDateString();

        $this->actingAs($user)
            ->post(route('guest.bookings.store'), [
                'room_id' => $room->id,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'adults' => 2,
                'children' => 1,
                'phone_number' => '+60123456789',
                'identification_number' => 'PORTAL-ID-001',
                'special_requests' => 'Quiet room',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('guest.portal'));

        $this->assertDatabaseHas('bookings', [
            'guest_id' => $guest->id,
            'booking_status' => 'pending',
            'booking_source' => 'website',
            'subtotal' => 750,
            'total_amount' => 750,
        ]);
        $this->assertDatabaseHas('booking_room_items', [
            'room_id' => $room->id,
            'nightly_rate' => 250,
        ]);
        $booking = Booking::query()->where('guest_id', $guest->id)->firstOrFail();
        $bookingItem = $booking->bookingRoomItems()->firstOrFail();
        $this->assertSame($checkIn, $bookingItem->check_in_date->format('Y-m-d'));
        $this->assertSame($checkOut, $bookingItem->check_out_date->format('Y-m-d'));
        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'phone_number' => '+60123456789',
            'identification_number' => 'PORTAL-ID-001',
        ]);
    }

    public function test_existing_account_must_verify_guest_details_before_viewing_booking_history(): void
    {
        $user = User::factory()->create(['email' => 'history@example.test']);
        $guest = Guest::factory()->create([
            'user_id' => null,
            'email' => $user->email,
            'phone_number' => '+60127778888',
            'identification_number' => 'HISTORY-ID-001',
        ]);
        Booking::factory()->create([
            'guest_id' => $guest->id,
            'booking_reference' => 'HISTORY-BOOKING-001',
        ]);

        $this->actingAs($user)
            ->get(route('guest.portal'))
            ->assertOk()
            ->assertSee('Confirm your booking details')
            ->assertDontSee('HISTORY-BOOKING-001');

        $this->post(route('guest.history.link'), [
            'phone_number' => '+60120000000',
            'identification_number' => 'WRONG-ID',
        ])->assertSessionHasErrors('identification_number');

        $this->assertNull($guest->fresh()->user_id);

        $this->post(route('guest.history.link'), [
            'phone_number' => '+60127778888',
            'identification_number' => 'HISTORY-ID-001',
        ])->assertRedirect(route('guest.portal'));

        $this->assertSame($user->id, $guest->fresh()->user_id);
        $this->get(route('guest.portal'))
            ->assertOk()
            ->assertSee('HISTORY-BOOKING-001');
    }
}