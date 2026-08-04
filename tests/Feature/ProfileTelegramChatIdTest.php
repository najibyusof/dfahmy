<?php

namespace Tests\Feature;

use App\Jobs\SendTelegramMessageJob;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProfileTelegramChatIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_user_can_update_telegram_chat_id_from_profile_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Manager');

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Updated User',
                'email' => $user->email,
                'telegram_chat_id' => '-1001234567890',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'telegram_chat_id' => '-1001234567890',
        ]);
    }

    public function test_profile_page_shows_telegram_chat_id_guidance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Manager');

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSeeText('Telegram chat ID')
            ->assertSeeText('getUpdates')
            ->assertSeeText("Do not use the bot's own ID")
            ->assertSeeText('Send Test Telegram To Me')
            ->assertSeeText('Save your Telegram chat ID first to enable the self-test button.');
    }

    public function test_user_can_queue_telegram_test_message_for_own_chat_id(): void
    {
        Queue::fake();

        $user = User::factory()->create(['telegram_chat_id' => '-1001234567890']);
        $user->assignRole('Manager');

        $this->actingAs($user)
            ->post(route('profile.telegram-test'))
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        Queue::assertPushed(SendTelegramMessageJob::class, function (SendTelegramMessageJob $job): bool {
            return $job->chatId === '-1001234567890'
                && str_contains($job->message, 'Telegram Profile Test');
        });

        $this->assertNotNull($user->telegram_test_queued_at);
    }

    public function test_user_cannot_queue_telegram_test_message_without_chat_id(): void
    {
        Queue::fake();

        $user = User::factory()->create(['telegram_chat_id' => null]);
        $user->assignRole('Manager');

        $this->actingAs($user)
            ->post(route('profile.telegram-test'))
            ->assertRedirect(route('profile.edit'));

        Queue::assertNothingPushed();
    }

    public function test_profile_page_disables_telegram_self_test_button_without_chat_id(): void
    {
        $user = User::factory()->create(['telegram_chat_id' => null]);
        $user->assignRole('Manager');

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Send Test Telegram To Me', false)
            ->assertSee('disabled', false);
    }
}