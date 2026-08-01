<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'building_id' => Building::factory(),
            'name' => 'Room ' . fake()->unique()->numberBetween(1, 999),
            'code' => strtoupper(fake()->unique()->lexify('RM???')),
            'floor' => fake()->numberBetween(1, 3),
            'room_type' => fake()->randomElement(['standard', 'suite', 'family']),
            'status' => fake()->randomElement(Room::STATUSES),
            'base_nightly_rate' => fake()->randomFloat(2, 100, 1500),
            'maximum_guests' => fake()->numberBetween(1, 8),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
