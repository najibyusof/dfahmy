<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingRoomItem;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRoomItem>
 */
class BookingRoomItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('-10 days', '+10 days');
        $checkOut = (clone $checkIn)->modify('+2 day');

        return [
            'booking_id' => Booking::factory(),
            'room_id' => Room::factory(),
            'nightly_rate' => fake()->randomFloat(2, 100, 1500),
            'adults' => fake()->numberBetween(1, 4),
            'children' => fake()->numberBetween(0, 3),
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
        ];
    }
}
