<?php

namespace Database\Factories;

use App\Models\BookableUnit;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookableUnit>
 */
class BookableUnitFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (BookableUnit $unit): void {
            if ($unit->rooms()->exists()) {
                return;
            }

            $room = Room::factory()->create([
                'base_nightly_rate' => (float) $unit->base_nightly_rate,
                'maximum_guests' => max(1, (int) $unit->maximum_guests),
            ]);

            $unit->rooms()->attach($room->id);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Unit ' . fake()->unique()->numerify('###'),
            'code' => strtoupper(fake()->unique()->lexify('UNIT-????')),
            'description' => fake()->sentence(),
            'booking_type' => fake()->randomElement(BookableUnit::TYPES),
            'base_nightly_rate' => fake()->randomFloat(2, 100, 3000),
            'maximum_guests' => fake()->numberBetween(1, 20),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 999),
        ];
    }
}
