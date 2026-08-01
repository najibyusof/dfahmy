<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\InAppSystemNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_user_can_view_notification_center_and_payload_contains_required_fields(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $manager->notify(new InAppSystemNotification([
            'title' => 'Demo Notification',
            'message' => 'This is a demo.',
            'type' => 'demo_type',
            'link' => route('dashboard'),
        ]));

        $response = $this->actingAs($manager)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Notification Centre')
            ->assertSee('Demo Notification')
            ->assertSee('Status: unread');

        $notification = $manager->notifications()->firstOrFail();

        $this->assertArrayHasKey('title', $notification->data);
        $this->assertArrayHasKey('message', $notification->data);
        $this->assertArrayHasKey('type', $notification->data);
        $this->assertArrayHasKey('link', $notification->data);
        $this->assertArrayHasKey('created_time', $notification->data);
        $this->assertArrayHasKey('read_status', $notification->data);

        $response->assertSee(route('notifications.index'));
    }

    public function test_user_can_mark_one_notification_as_read(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $manager->notify(new InAppSystemNotification([
            'title' => 'A',
            'message' => 'A message',
            'type' => 'a_type',
            'link' => route('dashboard'),
        ]));

        $notification = $manager->notifications()->firstOrFail();

        $this->actingAs($manager)
            ->post(route('notifications.mark-read', $notification->id))
            ->assertRedirect();

        $notification->refresh();

        $this->assertNotNull($notification->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $manager->notify(new InAppSystemNotification([
            'title' => 'A',
            'message' => 'A message',
            'type' => 'a_type',
            'link' => route('dashboard'),
        ]));

        $manager->notify(new InAppSystemNotification([
            'title' => 'B',
            'message' => 'B message',
            'type' => 'b_type',
            'link' => route('dashboard'),
        ]));

        $this->actingAs($manager)
            ->post(route('notifications.mark-all-read'))
            ->assertRedirect();

        $this->assertSame(0, $manager->fresh()->unreadNotifications()->count());
    }
}
