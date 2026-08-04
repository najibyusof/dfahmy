<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    public function isConfigured(): bool
    {
        $token = (string) config('services.telegram.bot_token');

        return trim($token) !== '';
    }

    public function recipientCount(): int
    {
        return $this->recipientChatIds()->count();
    }

    public function sendMessage(string $message): bool
    {
        if (! $this->isConfigured()) {
            Log::notice('Telegram delivery skipped because the bot token is not configured.', [
                'channel' => 'telegram',
            ]);

            return true;
        }

        $chatIds = $this->recipientChatIds();

        if ($chatIds->isEmpty()) {
            Log::notice('Telegram delivery skipped because no internal user has a Telegram chat ID configured.', [
                'channel' => 'telegram',
            ]);

            return true;
        }

        $allSent = true;

        foreach ($chatIds as $chatId) {
            if (! $this->sendMessageToChatId($message, $chatId)) {
                $allSent = false;
            }
        }

        return $allSent;
    }

    public function sendMessageToChatId(string $message, string $chatId): bool
    {
        if (! $this->isConfigured()) {
            Log::notice('Telegram delivery skipped because the bot token is not configured.', [
                'channel' => 'telegram',
            ]);

            return true;
        }

        if (trim($chatId) === '') {
            Log::notice('Telegram delivery skipped because the target chat ID is empty.', [
                'channel' => 'telegram',
            ]);

            return true;
        }

        return $this->sendMessageToResolvedChatId($message, $chatId);
    }

    /**
     * @return Collection<int, string>
     */
    private function recipientChatIds(): Collection
    {
        return User::query()
            ->whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->whereHas('roles', fn ($query) => $query->where('name', '!=', 'Guest'))
            ->orderBy('id')
            ->distinct()
            ->pluck('telegram_chat_id');
    }

    private function sendMessageToResolvedChatId(string $message, string $chatId): bool
    {
        $token = (string) config('services.telegram.bot_token');

        try {
            $response = Http::retry(3, 200, throw: false)
                ->asForm()
                ->post('https://api.telegram.org/bot' . $token . '/sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => true,
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Telegram delivery failed due to a connection or SSL error.', [
                'channel' => 'telegram',
                'exception_class' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }

        if ($response->successful()) {
            return true;
        }

        Log::warning('Telegram API responded with failure status.', [
            'channel' => 'telegram',
            'status' => $response->status(),
            'response_body' => $response->body(),
        ]);

        return false;
    }
}
