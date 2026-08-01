<?php

namespace Tests\Feature;

use App\Models\HousekeepingTask;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_user_without_role_cannot_access_protected_module_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('modules.rooms.index'))->assertForbidden();
        $this->actingAs($user)->get(route('modules.bookings.index'))->assertForbidden();
        $this->actingAs($user)->get(route('modules.payments.index'))->assertForbidden();
        $this->actingAs($user)->get(route('modules.reports.index'))->assertForbidden();
    }

    public function test_receptionist_cannot_access_manager_only_modules(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Receptionist');

        $this->actingAs($user)->get(route('modules.maintenance.index'))->assertForbidden();
        $this->actingAs($user)->get(route('modules.reports.index'))->assertForbidden();
        $this->actingAs($user)->get(route('modules.housekeeping.index'))->assertForbidden();
    }

    public function test_housekeeper_can_view_assigned_tasks_but_not_management_module(): void
    {
        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $otherUser = User::factory()->create();

        HousekeepingTask::query()->create([
            'assigned_to_user_id' => $housekeeper->id,
            'room_label' => 'A-101',
            'status' => 'pending',
            'notes' => 'Daily cleaning',
        ]);

        $foreignTask = HousekeepingTask::query()->create([
            'assigned_to_user_id' => $otherUser->id,
            'room_label' => 'B-220',
            'status' => 'pending',
            'notes' => 'Deep clean',
        ]);

        $this->actingAs($housekeeper)->get(route('housekeeping.tasks.index'))->assertOk();
        $this->actingAs($housekeeper)->get(route('modules.housekeeping.index'))->assertForbidden();

        $this->actingAs($housekeeper)
            ->patch(route('housekeeping.tasks.update', $foreignTask), ['status' => 'completed'])
            ->assertForbidden();
    }

    public function test_navigation_hides_unauthorized_items(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Receptionist');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertSee('Guests');
        $response->assertSee('Bookings');
        $response->assertSee('Payments');
        $response->assertDontSee('Maintenance');
        $response->assertDontSee('Reports');
        $response->assertDontSee('Housekeeping Management');
    }
}
