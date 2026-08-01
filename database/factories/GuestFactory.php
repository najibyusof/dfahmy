<?php

namespace Database\Factories;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->unique()->numerify('+6012#######'),
            'identification_number' => strtoupper(fake()->unique()->bothify('ID########')),
            'address' => fake()->address(),
            'nationality' => fake()->country(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->numerify('+6019#######'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
