<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use App\Notifications\HousekeepingTaskAssignedNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HousekeepingModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_checkout_creates_cleaning_task_and_sets_room_to_cleaning(): void
    {
        Notification::fake();

        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $room = Room::factory()->create(['status' => 'available']);

        $booking = Booking::factory()->create([
            'booking_status' => 'checked_in',
            'check_in_date' => now()->subDay()->toDateString(),
            'check_out_date' => now()->toDateString(),
        ]);

        $booking->bookingRoomItems()->delete();
        $booking->bookingRoomItems()->create([
            'room_id' => $room->id,
            'nightly_rate' => 100,
            'adults' => 1,
            'children' => 0,
            'check_in_date' => now()->subDay()->toDateString(),
            'check_out_date' => now()->toDateString(),
        ]);

        $this->actingAs($manager)
            ->patch(route('bookings.check-out', $booking))
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertDatabaseHas('housekeeping_tasks', [
            'booking_id' => $booking->id,
            'room_id' => $room->id,
            'task_type' => 'checkout_cleaning',
            'status' => 'pending',
            'assigned_to_user_id' => $housekeeper->id,
        ]);

        $room->refresh();
        $this->assertSame('cleaning', $room->status);

        Notification::assertSentTo($housekeeper, HousekeepingTaskAssignedNotification::class);
    }

    public function test_manager_can_create_and_assign_housekeeping_task(): void
    {
        Notification::fake();

        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $room = Room::factory()->create();

        $this->actingAs($manager)
            ->post(route('housekeeping.manage.store'), [
                'room_id' => $room->id,
                'booking_id' => null,
                'assigned_to_user_id' => $housekeeper->id,
                'task_type' => 'deep_cleaning',
                'priority' => 'medium',
                'due_date' => now()->addDay()->toDateString(),
                'status' => 'pending',
                'notes' => 'Manager created task',
                'checklist_notes' => 'Replace towels',
                'completed_at' => null,
            ])
            ->assertRedirect(route('housekeeping.manage.index'));

        $this->assertDatabaseHas('housekeeping_tasks', [
            'room_id' => $room->id,
            'assigned_to_user_id' => $housekeeper->id,
            'task_type' => 'deep_cleaning',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        Notification::assertSentTo($housekeeper, HousekeepingTaskAssignedNotification::class);
    }

    public function test_housekeeper_sees_only_own_assigned_tasks(): void
    {
        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $anotherHousekeeper = User::factory()->create();
        $anotherHousekeeper->assignRole('Housekeeper');

        $myTask = HousekeepingTask::factory()->create([
            'assigned_to_user_id' => $housekeeper->id,
            'room_label' => 'MY-ROOM',
            'status' => 'pending',
        ]);

        $otherTask = HousekeepingTask::factory()->create([
            'assigned_to_user_id' => $anotherHousekeeper->id,
            'room_label' => 'OTHER-ROOM',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($housekeeper)
            ->get(route('housekeeping.tasks.index'))
            ->assertOk();

        $myTask->load('room');
        $otherTask->load('room');

        $response->assertSee($myTask->room?->code ?? $myTask->room_label);
        $response->assertDontSee($otherTask->room?->code ?? $otherTask->room_label);
    }

    public function test_housekeeper_cannot_set_disallowed_status(): void
    {
        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $task = HousekeepingTask::factory()->create([
            'assigned_to_user_id' => $housekeeper->id,
            'status' => 'pending',
        ]);

        $this->actingAs($housekeeper)
            ->from(route('housekeeping.tasks.index'))
            ->patch(route('housekeeping.tasks.update', $task), [
                'status' => 'cancelled',
                'checklist_notes' => 'Trying to cancel',
            ])
            ->assertRedirect(route('housekeeping.tasks.index'))
            ->assertSessionHasErrors(['status']);

        $task->refresh();
        $this->assertSame('pending', $task->status);
    }

    public function test_completed_checkout_cleaning_sets_room_to_available_when_no_blockers(): void
    {
        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $room = Room::factory()->create(['status' => 'cleaning']);

        $task = HousekeepingTask::factory()->create([
            'assigned_to_user_id' => $housekeeper->id,
            'room_id' => $room->id,
            'task_type' => 'checkout_cleaning',
            'status' => 'in_progress',
            'completed_at' => null,
        ]);

        $this->actingAs($housekeeper)
            ->patch(route('housekeeping.tasks.update', $task), [
                'status' => 'completed',
                'checklist_notes' => 'Completed all checklist items',
            ])
            ->assertRedirect(route('housekeeping.tasks.index'));

        $task->refresh();
        $room->refresh();

        $this->assertSame('completed', $task->status);
        $this->assertNotNull($task->completed_at);
        $this->assertSame('available', $room->status);
    }

    public function test_completed_checkout_cleaning_sets_room_to_maintenance_when_open_maintenance_task_exists(): void
    {
        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $room = Room::factory()->create(['status' => 'cleaning']);

        HousekeepingTask::factory()->create([
            'assigned_to_user_id' => $housekeeper->id,
            'room_id' => $room->id,
            'task_type' => 'maintenance',
            'status' => 'pending',
        ]);

        $checkoutTask = HousekeepingTask::factory()->create([
            'assigned_to_user_id' => $housekeeper->id,
            'room_id' => $room->id,
            'task_type' => 'checkout_cleaning',
            'status' => 'in_progress',
        ]);

        $this->actingAs($housekeeper)
            ->patch(route('housekeeping.tasks.update', $checkoutTask), [
                'status' => 'completed',
                'checklist_notes' => 'Cleaning complete; maintenance required',
            ])
            ->assertRedirect(route('housekeeping.tasks.index'));

        $room->refresh();
        $this->assertSame('maintenance', $room->status);
    }

    public function test_completed_checkout_cleaning_sets_room_to_reserved_when_incoming_reservation_exists(): void
    {
        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $room = Room::factory()->create(['status' => 'cleaning']);

        $checkoutTask = HousekeepingTask::factory()->create([
            'assigned_to_user_id' => $housekeeper->id,
            'room_id' => $room->id,
            'task_type' => 'checkout_cleaning',
            'status' => 'in_progress',
        ]);

        $incomingBooking = Booking::factory()->create([
            'booking_status' => 'confirmed',
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
        ]);

        $incomingBooking->bookingRoomItems()->delete();
        $incomingBooking->bookingRoomItems()->create([
            'room_id' => $room->id,
            'nightly_rate' => 150,
            'adults' => 2,
            'children' => 0,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($housekeeper)
            ->patch(route('housekeeping.tasks.update', $checkoutTask), [
                'status' => 'completed',
                'checklist_notes' => 'Prepared room for next guest',
            ])
            ->assertRedirect(route('housekeeping.tasks.index'));

        $room->refresh();
        $this->assertSame('reserved', $room->status);
    }
}
