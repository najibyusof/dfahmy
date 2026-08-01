<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Booking $booking): void {
            if ($booking->bookingRoomItems()->exists()) {
                return;
            }

            $room = Room::factory()->create();

            $booking->bookingRoomItems()->create([
                'room_id' => $room->id,
                'nightly_rate' => $room->base_nightly_rate,
                'adults' => (int) $booking->adults,
                'children' => (int) $booking->children,
                'check_in_date' => $booking->check_in_date->format('Y-m-d'),
                'check_out_date' => $booking->check_out_date->format('Y-m-d'),
            ]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('-30 days', '+30 days');
        $checkOut = (clone $checkIn)->modify('+' . fake()->numberBetween(1, 5) . ' day');

        return [
            'guest_id' => Guest::factory(),
            'booking_reference' => strtoupper(fake()->unique()->bothify('BK####??')),
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'adults' => fake()->numberBetween(1, 4),
            'children' => fake()->numberBetween(0, 3),
            'booking_source' => fake()->randomElement(Booking::SOURCES),
            'booking_status' => fake()->randomElement(Booking::STATUSES),
            'special_requests' => fake()->optional()->sentence(),
            'internal_notes' => fake()->optional()->sentence(),
            'subtotal' => fake()->randomFloat(2, 200, 5000),
            'discount' => fake()->randomFloat(2, 0, 200),
            'tax' => fake()->randomFloat(2, 0, 300),
            'total_amount' => fake()->randomFloat(2, 200, 6000),
        ];
    }
}
