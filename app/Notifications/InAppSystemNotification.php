<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InAppSystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(private readonly array $payload) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => (string) ($this->payload['title'] ?? 'Notification'),
            'message' => (string) ($this->payload['message'] ?? ''),
            'type' => (string) ($this->payload['type'] ?? 'info'),
            'link' => (string) ($this->payload['link'] ?? route('dashboard')),
            'created_time' => (string) ($this->payload['created_time'] ?? now()->toIso8601String()),
            'read_status' => false,
            'meta' => $this->payload['meta'] ?? [],
        ];
    }
}
