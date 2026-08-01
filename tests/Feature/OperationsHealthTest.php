<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OperationsHealthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_non_admin_user_cannot_access_operations_health_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Manager');

        $this->actingAs($user)
            ->get(route('admin.operations-health.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_operations_health_with_queue_and_scheduler_metrics(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        Cache::put('system.scheduler_heartbeat_at', now()->subMinute()->toIso8601String());

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'TestJob'], JSON_THROW_ON_ERROR),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->subMinutes(5)->timestamp,
        ]);

        DB::table('failed_jobs')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{"job":"demo"}',
            'exception' => 'Example exception',
            'failed_at' => now(),
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.operations-health.index'))
            ->assertOk()
            ->assertSeeText('Operations Health')
            ->assertSeeText('Failed Jobs')
            ->assertSeeText('Pending Jobs')
            ->assertSeeText('Scheduler Heartbeat')
            ->assertSeeText('Operational Checks');
    }
}
