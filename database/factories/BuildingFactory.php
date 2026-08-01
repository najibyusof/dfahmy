<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Building>
 */
class BuildingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company() . ' House',
            'code' => strtoupper(fake()->unique()->lexify('BLD???')),
            'description' => fake()->optional()->sentence(),
            'number_of_floors' => fake()->numberBetween(1, 4),
            'is_active' => true,
        ];
    }
}
