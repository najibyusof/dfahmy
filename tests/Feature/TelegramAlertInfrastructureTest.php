<?php

namespace Tests\Feature;

use App\Jobs\SendTelegramMessageJob;
use App\Models\Booking;
use App\Models\OperationAlertSetting;
use App\Models\User;
use App\Services\TelegramAlertService;
use App\Services\TelegramBotService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TelegramAlertInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        config()->set('services.telegram.bot_token', 'test-bot-token');
    }

    public function test_telegram_job_sends_http_request_with_retries_using_fake_http(): void
    {
        $superAdmin = User::factory()->create(['telegram_chat_id' => '111111']);
        $superAdmin->assignRole('Super Admin');

        $manager = User::factory()->create(['telegram_chat_id' => '222222']);
        $manager->assignRole('Manager');

        User::factory()->create(['telegram_chat_id' => '333333']);

        $housekeeperWithoutChatId = User::factory()->create(['telegram_chat_id' => null]);
        $housekeeperWithoutChatId->assignRole('Housekeeper');

        Http::fake([
            'https://api.telegram.org/*' => Http::sequence()
                ->push(['ok' => false], 500)
                ->push(['ok' => false], 500)
                ->push(['ok' => true], 200)
                ->push(['ok' => true], 200)
                ->push(['ok' => true], 200)
                ->push(['ok' => false], 500)
                ->push(['ok' => false], 500)
                ->push(['ok' => true], 200),
        ]);

        SendTelegramMessageJob::dispatchSync('*Hello from test*');

        Http::assertSentCount(4);

        $chatIds = [];
        Http::assertSent(function (Request $request) use (&$chatIds): bool {
            if (! str_contains($request->url(), '/bottest-bot-token/sendMessage')) {
                return false;
            }

            $chatIds[] = $request['chat_id'];

            return $request['parse_mode'] === 'Markdown';
        });

        $this->assertSame(['111111', '111111', '111111', '222222'], $chatIds);
    }

    public function test_telegram_service_logs_connection_or_ssl_failures_clearly(): void
    {
        $manager = User::factory()->create(['telegram_chat_id' => '123456']);
        $manager->assignRole('Manager');

        Log::spy();

        Http::fake(function (): never {
            throw new ConnectionException('cURL error 60: SSL certificate problem');
        });

        $sent = app(TelegramBotService::class)->sendMessage('*Hello from test*');

        $this->assertFalse($sent);

        Log::shouldHaveReceived('warning')->once()->withArgs(function (string $message, array $context): bool {
            return $message === 'Telegram delivery failed due to a connection or SSL error.'
                && ($context['channel'] ?? null) === 'telegram'
                && ($context['exception_class'] ?? null) === ConnectionException::class
                && str_contains((string) ($context['message'] ?? ''), 'SSL certificate problem');
        });
    }

    public function test_telegram_service_logs_api_failure_response_body(): void
    {
        $manager = User::factory()->create(['telegram_chat_id' => '123456']);
        $manager->assignRole('Manager');

        Log::spy();

        Http::fake([
            'https://api.telegram.org/*' => Http::response([
                'ok' => false,
                'error_code' => 403,
                'description' => "Forbidden: the bot can't send messages to the bot",
            ], 403),
        ]);

        $sent = app(TelegramBotService::class)->sendMessage('*Hello from test*');

        $this->assertFalse($sent);

        Log::shouldHaveReceived('warning')->once()->withArgs(function (string $message, array $context): bool {
            return $message === 'Telegram API responded with failure status.'
                && ($context['channel'] ?? null) === 'telegram'
                && ($context['status'] ?? null) === 403
                && str_contains((string) ($context['response_body'] ?? ''), "the bot can't send messages to the bot");
        });
    }

    public function test_telegram_alert_service_dispatches_job_when_enabled_and_skips_when_disabled(): void
    {
        Queue::fake();

        $service = app(TelegramAlertService::class);
        $booking = Booking::factory()->create();
        $booking->load('guest');

        $service->newBooking($booking);

        Queue::assertPushed(SendTelegramMessageJob::class);

        Queue::fake();
        OperationAlertSetting::query()->create([
            'key' => 'telegram_new_booking',
            'enabled' => false,
        ]);

        $service->newBooking($booking);

        Queue::assertNotPushed(SendTelegramMessageJob::class);
    }

    public function test_super_admin_can_manage_telegram_alert_settings_and_non_super_admin_is_forbidden(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $this->actingAs($manager)
            ->get(route('admin.telegram-alert-settings.index'))
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->get(route('admin.telegram-alert-settings.index'))
            ->assertOk()
            ->assertSee('Telegram Alert Settings')
            ->assertSee('Admin setup guide')
            ->assertSee('TELEGRAM_BOT_TOKEN')
            ->assertSee('Profile')
            ->assertSee('HEALTH_CHECK_TOKEN');

        $payload = [
            'telegram_new_booking' => 1,
            'telegram_booking_cancellation' => 0,
            'telegram_check_in' => 1,
            'telegram_check_out' => 1,
            'telegram_overdue_payment_outstanding_balance' => 0,
            'telegram_urgent_housekeeping_task' => 1,
            'telegram_urgent_maintenance_request' => 0,
        ];

        $this->actingAs($superAdmin)
            ->patch(route('admin.telegram-alert-settings.update'), $payload)
            ->assertRedirect(route('admin.telegram-alert-settings.index'));

        $this->assertDatabaseHas('operation_alert_settings', [
            'key' => 'telegram_new_booking',
            'enabled' => true,
        ]);

        $this->assertDatabaseHas('operation_alert_settings', [
            'key' => 'telegram_booking_cancellation',
            'enabled' => false,
        ]);
    }

    public function test_super_admin_can_queue_test_telegram_alert_and_manager_cannot(): void
    {
        Queue::fake();

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $manager = User::factory()->create();
        $manager->assignRole('Manager');

        $this->actingAs($manager)
            ->post(route('admin.telegram-alert-settings.test'))
            ->assertForbidden();

        Queue::assertNotPushed(SendTelegramMessageJob::class);

        $this->actingAs($superAdmin)
            ->post(route('admin.telegram-alert-settings.test'))
            ->assertRedirect(route('admin.telegram-alert-settings.index'));

        Queue::assertPushed(SendTelegramMessageJob::class);
    }
}
