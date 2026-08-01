<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'receipt_number' => strtoupper(fake()->unique()->bothify('RCPT-######')),
            'payment_date' => fake()->dateTimeBetween('-20 days', 'now')->format('Y-m-d'),
            'amount' => fake()->randomFloat(2, 50, 1500),
            'payment_method' => fake()->randomElement(Payment::METHODS),
            'reference_number' => fake()->optional()->bothify('REF-####??'),
            'payment_status' => fake()->randomElement(Payment::STATUSES),
            'received_by_user_id' => User::factory(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
