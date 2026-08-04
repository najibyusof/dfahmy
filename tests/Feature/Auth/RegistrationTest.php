<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+60123456789',
            'identification_number' => 'TEST-ID-001',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('guest.portal'));

        $this->assertDatabaseHas('guests', [
            'user_id' => auth()->id(),
            'email' => 'test@example.com',
            'phone_number' => '+60123456789',
            'identification_number' => 'TEST-ID-001',
        ]);

        $this->get(route('guest.portal'))
            ->assertOk()
            ->assertSee('Available dates')
            ->assertSee('My bookings');

        $this->get('/register')->assertRedirect(route('guest.portal'));
    }
}
