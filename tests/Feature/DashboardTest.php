<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_guests_are_redirected_to_login_when_visiting_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_verified_users_can_view_the_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Manager');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Home Dashboard');
        $response->assertSee('DFahMy Eco Resort');
    }

    public function test_unverified_users_cannot_view_the_dashboard(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('Manager');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertForbidden();
    }
}
