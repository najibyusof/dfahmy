<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    public function isConfigured(): bool
    {
        $token = (string) config('services.telegram.bot_token');
        $chatId = (string) config('services.telegram.chat_id');

        return trim($token) !== '' && trim($chatId) !== '';
    }

    public function sendMessage(string $message): bool
    {
        if (! $this->isConfigured()) {
            Log::notice('Telegram delivery skipped because configuration is incomplete.', [
                'channel' => 'telegram',
            ]);

            return true;
        }

        $token = (string) config('services.telegram.bot_token');
        $chatId = (string) config('services.telegram.chat_id');

        $response = Http::retry(3, 200, throw: false)
            ->asForm()
            ->post('https://api.telegram.org/bot' . $token . '/sendMessage', [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true,
            ]);

        if ($response->successful()) {
            return true;
        }

        Log::warning('Telegram API responded with failure status.', [
            'channel' => 'telegram',
            'status' => $response->status(),
        ]);

        return false;
    }
}
