<?php

namespace App\Jobs;

use App\Services\TelegramBotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [30, 120, 300];

    public function __construct(
        public readonly string $message,
        public readonly ?string $chatId = null,
    ) {}

    public function handle(TelegramBotService $telegramBotService): void
    {
        $sent = $this->chatId !== null
            ? $telegramBotService->sendMessageToChatId($this->message, $this->chatId)
            : $telegramBotService->sendMessage($this->message);

        if (! $sent) {
            Log::warning('Telegram delivery was not successful for queued message.', [
                'channel' => 'telegram',
            ]);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Log::warning('Telegram message failed after retries.', [
            'channel' => 'telegram',
            'exception_class' => $exception?->getMessage() !== null ? $exception::class : null,
        ]);
    }
}
