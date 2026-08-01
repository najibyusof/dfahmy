<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        /** @var DatabaseNotification $record */
        $record = $request->user()->notifications()->findOrFail($notification);

        if ($record->read_at === null) {
            $record->markAsRead();
        }

        return redirect()->back()->with('status', 'notification-marked-read');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('status', 'notifications-marked-read');
    }
}
