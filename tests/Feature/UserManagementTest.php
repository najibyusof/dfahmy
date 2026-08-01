<?php

namespace Tests\Feature;

use App\Models\RoleAssignmentAudit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_non_admin_user_cannot_access_user_management_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Receptionist');

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_non_admin_user_cannot_update_user_role(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole('Manager');

        $target = User::factory()->create();

        $this->actingAs($actor)
            ->patch(route('admin.users.role.update', $target), ['role' => 'Receptionist'])
            ->assertForbidden();
    }

    public function test_non_super_admin_cannot_access_add_system_user_page_or_submit(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $payload = [
            'name' => 'New System User',
            'email' => 'new.system.user@example.com',
            'role' => 'Receptionist',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $this->actingAs($manager)
            ->get(route('admin.users.create'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->post(route('admin.users.store'), $payload)
            ->assertForbidden();
    }

    public function test_super_admin_can_create_system_user_with_role_assignment(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->actingAs($superAdmin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSeeText('Add System User');

        $payload = [
            'name' => 'Reception User',
            'email' => 'reception.user@example.com',
            'role' => 'Receptionist',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $this->actingAs($superAdmin)
            ->post(route('admin.users.store'), $payload)
            ->assertRedirect(route('admin.users.index'));

        $createdUser = User::query()->where('email', 'reception.user@example.com')->first();

        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('Receptionist'));
        $this->assertNotNull($createdUser->email_verified_at);
    }

    public function test_super_admin_can_view_user_management_page_and_update_roles(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $target = User::factory()->create();
        $target->assignRole('Housekeeper');

        $this->actingAs($superAdmin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('User Management')
            ->assertSee($target->email);

        $this->actingAs($superAdmin)
            ->patch(route('admin.users.role.update', $target), ['role' => 'Manager'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue($target->fresh()->hasRole('Manager'));

        $this->assertDatabaseHas('role_assignment_audits', [
            'actor_user_id' => $superAdmin->id,
            'target_user_id' => $target->id,
            'from_role' => 'Housekeeper',
            'to_role' => 'Manager',
        ]);
    }

    public function test_super_admin_cannot_change_own_role_from_user_management_screen(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->actingAs($superAdmin)
            ->patch(route('admin.users.role.update', $superAdmin), ['role' => 'Manager'])
            ->assertForbidden();

        $this->assertTrue($superAdmin->fresh()->hasRole('Super Admin'));
    }

    public function test_navigation_shows_user_management_only_when_authorized(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertDontSee('User Management');

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertSee('User Management');

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertDontSee('Roles Matrix');

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertSee('Roles Matrix');
    }

    public function test_non_admin_user_cannot_view_roles_permissions_matrix(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Manager');

        $this->actingAs($user)
            ->get(route('admin.roles-matrix.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_roles_permissions_matrix(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->actingAs($user)
            ->get(route('admin.roles-matrix.index'))
            ->assertOk()
            ->assertSeeText('Roles & Permissions Matrix')
            ->assertSeeText('dashboard.view')
            ->assertSeeText('users.manage');
    }

    public function test_role_assignment_audit_is_not_created_when_role_does_not_change(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $target = User::factory()->create();
        $target->assignRole('Manager');

        $this->actingAs($superAdmin)
            ->patch(route('admin.users.role.update', $target), ['role' => 'Manager'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertEquals(0, RoleAssignmentAudit::query()->count());
    }

    public function test_super_admin_can_filter_audit_logs_by_actor(): void
    {
        $superAdmin = User::factory()->create(['name' => 'Super Admin Root']);
        $superAdmin->assignRole('Super Admin');

        $otherAdmin = User::factory()->create(['name' => 'Other Admin']);
        $otherAdmin->assignRole('Super Admin');

        $targetA = User::factory()->create();
        $targetA->assignRole('Housekeeper');
        $targetB = User::factory()->create();
        $targetB->assignRole('Housekeeper');

        $this->actingAs($superAdmin)
            ->patch(route('admin.users.role.update', $targetA), ['role' => 'Manager'])
            ->assertRedirect(route('admin.users.index'));

        $this->actingAs($otherAdmin)
            ->patch(route('admin.users.role.update', $targetB), ['role' => 'Manager'])
            ->assertRedirect(route('admin.users.index'));

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.users.index', ['actor' => 'Other Admin']));

        $response->assertOk();
        $response->assertViewHas('auditLogs', function ($auditLogs) use ($otherAdmin): bool {
            return $auditLogs->total() === 1
                && (int) $auditLogs->first()->actor_user_id === (int) $otherAdmin->id;
        });
    }

    public function test_non_admin_user_cannot_export_audit_csv(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Manager');

        $this->actingAs($user)
            ->get(route('admin.users.audit.export'))
            ->assertForbidden();
    }

    public function test_super_admin_can_export_audit_csv(): void
    {
        $superAdmin = User::factory()->create(['name' => 'Admin CSV']);
        $superAdmin->assignRole('Super Admin');

        $target = User::factory()->create(['name' => 'Target CSV']);
        $target->assignRole('Housekeeper');

        $this->actingAs($superAdmin)
            ->patch(route('admin.users.role.update', $target), ['role' => 'Manager'])
            ->assertRedirect(route('admin.users.index'));

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.users.audit.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Changed By', $csv);
        $this->assertStringContainsString('Admin CSV', $csv);
        $this->assertStringContainsString('Target CSV', $csv);
    }

    public function test_super_admin_can_filter_audit_logs_by_from_and_to_role(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $actor = User::factory()->create(['name' => 'Actor Role']);
        $target = User::factory()->create(['name' => 'Target Role']);

        RoleAssignmentAudit::query()->create([
            'actor_user_id' => $actor->id,
            'target_user_id' => $target->id,
            'from_role' => 'Housekeeper',
            'to_role' => 'Manager',
        ]);

        RoleAssignmentAudit::query()->create([
            'actor_user_id' => $actor->id,
            'target_user_id' => $target->id,
            'from_role' => 'Receptionist',
            'to_role' => 'Super Admin',
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.users.index', [
                'from_role' => 'Housekeeper',
                'to_role' => 'Manager',
            ]));

        $response->assertOk();
        $response->assertViewHas('auditLogs', function ($auditLogs): bool {
            return $auditLogs->total() === 1
                && $auditLogs->first()->from_role === 'Housekeeper'
                && $auditLogs->first()->to_role === 'Manager';
        });
    }

    public function test_super_admin_can_sort_audit_logs_oldest_first(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $actor = User::factory()->create();
        $target = User::factory()->create();

        $older = RoleAssignmentAudit::query()->create([
            'actor_user_id' => $actor->id,
            'target_user_id' => $target->id,
            'from_role' => 'Housekeeper',
            'to_role' => 'Manager',
        ]);

        $newer = RoleAssignmentAudit::query()->create([
            'actor_user_id' => $actor->id,
            'target_user_id' => $target->id,
            'from_role' => 'Manager',
            'to_role' => 'Super Admin',
        ]);

        $older->forceFill([
            'created_at' => Carbon::parse('2026-01-01 09:00:00'),
            'updated_at' => Carbon::parse('2026-01-01 09:00:00'),
        ])->save();
        $newer->forceFill([
            'created_at' => Carbon::parse('2026-02-01 09:00:00'),
            'updated_at' => Carbon::parse('2026-02-01 09:00:00'),
        ])->save();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.users.index', ['sort' => 'created_at_asc']));

        $response->assertOk();
        $response->assertViewHas('auditLogs', function ($auditLogs) use ($older): bool {
            return (int) $auditLogs->first()->id === (int) $older->id;
        });
    }

    public function test_super_admin_can_choose_audit_logs_page_size(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $actor = User::factory()->create();

        for ($i = 0; $i < 15; $i++) {
            $target = User::factory()->create();

            RoleAssignmentAudit::query()->create([
                'actor_user_id' => $actor->id,
                'target_user_id' => $target->id,
                'from_role' => 'Housekeeper',
                'to_role' => 'Manager',
            ]);
        }

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.users.index', ['per_page' => 25]));

        $response->assertOk();
        $response->assertViewHas('auditLogs', function ($auditLogs): bool {
            return $auditLogs->perPage() === 25;
        });
    }

    public function test_invalid_date_range_returns_validation_error(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->actingAs($superAdmin)
            ->from(route('admin.users.index'))
            ->get(route('admin.users.index', [
                'from_date' => '2026-08-10',
                'to_date' => '2026-08-01',
            ]))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors(['from_date', 'to_date']);
    }

    public function test_quick_preset_date_range_can_filter_audit_results(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-01 10:00:00'));

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $actor = User::factory()->create();
        $target = User::factory()->create();

        $insideRange = RoleAssignmentAudit::query()->create([
            'actor_user_id' => $actor->id,
            'target_user_id' => $target->id,
            'from_role' => 'Housekeeper',
            'to_role' => 'Manager',
        ]);
        $insideRange->forceFill([
            'created_at' => Carbon::parse('2026-07-29 10:00:00'),
            'updated_at' => Carbon::parse('2026-07-29 10:00:00'),
        ])->save();

        $outsideRange = RoleAssignmentAudit::query()->create([
            'actor_user_id' => $actor->id,
            'target_user_id' => $target->id,
            'from_role' => 'Manager',
            'to_role' => 'Super Admin',
        ]);
        $outsideRange->forceFill([
            'created_at' => Carbon::parse('2026-06-20 10:00:00'),
            'updated_at' => Carbon::parse('2026-06-20 10:00:00'),
        ])->save();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.users.index', [
                'from_date' => '2026-07-26',
                'to_date' => '2026-08-01',
            ]));

        $response->assertOk();
        $response->assertViewHas('auditLogs', function ($auditLogs) use ($insideRange): bool {
            return $auditLogs->total() === 1
                && (int) $auditLogs->first()->id === (int) $insideRange->id;
        });

        Carbon::setTestNow();
    }
}
