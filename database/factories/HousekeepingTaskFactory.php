<?php

namespace Database\Factories;

use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HousekeepingTask>
 */
class HousekeepingTaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $room = Room::factory()->create();

        return [
            'assigned_to_user_id' => User::factory(),
            'room_id' => $room->id,
            'booking_id' => null,
            'room_label' => $room->code,
            'task_type' => fake()->randomElement(HousekeepingTask::TASK_TYPES),
            'priority' => fake()->randomElement(HousekeepingTask::PRIORITIES),
            'due_date' => fake()->dateTimeBetween('now', '+5 days')->format('Y-m-d'),
            'status' => fake()->randomElement(HousekeepingTask::STATUSES),
            'notes' => fake()->optional()->sentence(),
            'checklist_notes' => fake()->optional()->sentence(),
            'completed_at' => null,
        ];
    }
}
