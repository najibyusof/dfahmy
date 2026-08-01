<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_housekeeper_cannot_access_guest_module_routes(): void
    {
        $housekeeper = User::factory()->create();
        $housekeeper->assignRole('Housekeeper');

        $guest = Guest::factory()->create();

        $this->actingAs($housekeeper)->get(route('guests.index'))->assertForbidden();
        $this->actingAs($housekeeper)->get(route('guests.create'))->assertForbidden();
        $this->actingAs($housekeeper)->get(route('guests.show', $guest))->assertForbidden();
    }

    public function test_receptionist_can_create_search_update_and_delete_guest(): void
    {
        $receptionist = User::factory()->create();
        $receptionist->assignRole('Receptionist');

        $this->actingAs($receptionist)->post(route('guests.store'), [
            'full_name' => 'Alya Rahman',
            'email' => 'alya@example.test',
            'phone_number' => '+60121234567',
            'identification_number' => 'P1234567',
            'address' => 'Langkawi',
            'nationality' => 'Malaysia',
            'emergency_contact_name' => 'Ibrahim',
            'emergency_contact_phone' => '+60199887766',
            'notes' => 'Vegetarian meal preference',
        ])->assertRedirect(route('guests.index'));

        $guest = Guest::query()->where('email', 'alya@example.test')->firstOrFail();

        $this->actingAs($receptionist)->patch(route('guests.update', $guest), [
            'full_name' => 'Alya Rahman Updated',
            'email' => 'alya.updated@example.test',
            'phone_number' => '+60125555555',
            'identification_number' => 'P7654321',
            'address' => 'Kuah',
            'nationality' => 'Malaysia',
            'emergency_contact_name' => 'Hana',
            'emergency_contact_phone' => '+60197777777',
            'notes' => 'Late check-in',
        ])->assertRedirect(route('guests.index'));

        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'full_name' => 'Alya Rahman Updated',
            'email' => 'alya.updated@example.test',
            'phone_number' => '+60125555555',
            'identification_number' => 'P7654321',
        ]);

        $this->actingAs($receptionist)
            ->get(route('guests.index', ['search' => 'P7654321']))
            ->assertOk()
            ->assertSee('Alya Rahman Updated');

        $this->actingAs($receptionist)->delete(route('guests.destroy', $guest))
            ->assertRedirect(route('guests.index'));

        $this->assertDatabaseMissing('guests', ['id' => $guest->id]);
    }

    public function test_guest_duplicate_detection_works_for_email_phone_and_identification_number(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        Guest::factory()->create([
            'email' => 'duplicate@example.test',
            'phone_number' => '+60123334444',
            'identification_number' => 'PASS1234',
        ]);

        $this->actingAs($manager)
            ->from(route('guests.create'))
            ->post(route('guests.store'), [
                'full_name' => 'Duplicate Guest',
                'email' => 'duplicate@example.test',
                'phone_number' => '+60123334444',
                'identification_number' => 'PASS1234',
            ])
            ->assertRedirect(route('guests.create'))
            ->assertSessionHasErrors(['email', 'phone_number', 'identification_number']);
    }

    public function test_guest_profile_shows_upcoming_and_past_bookings(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create(['full_name' => 'Nur Hanis']);

        Booking::factory()->create([
            'guest_id' => $guest->id,
            'booking_reference' => 'UPCOMING-001',
            'check_in_date' => now()->addDays(5)->toDateString(),
            'check_out_date' => now()->addDays(7)->toDateString(),
            'booking_status' => 'confirmed',
        ]);

        Booking::factory()->create([
            'guest_id' => $guest->id,
            'booking_reference' => 'PAST-001',
            'check_in_date' => now()->subDays(9)->toDateString(),
            'check_out_date' => now()->subDays(7)->toDateString(),
            'booking_status' => 'checked_out',
        ]);

        $response = $this->actingAs($manager)->get(route('guests.show', $guest));

        $response->assertOk();
        $response->assertSee('Guest Profile');
        $response->assertSee('Nur Hanis');
        $response->assertSee('Upcoming Bookings');
        $response->assertSee('Past Bookings');
        $response->assertSee('UPCOMING-001');
        $response->assertSee('PAST-001');
    }

    public function test_guest_delete_is_blocked_when_bookings_exist(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $guest = Guest::factory()->create();

        Booking::factory()->create([
            'guest_id' => $guest->id,
            'booking_reference' => 'LOCK-001',
        ]);

        $this->actingAs($manager)
            ->delete(route('guests.destroy', $guest))
            ->assertRedirect(route('guests.index'));

        $this->assertDatabaseHas('guests', ['id' => $guest->id]);
    }
}
