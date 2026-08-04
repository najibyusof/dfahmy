<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\TelegramAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function sendTelegramTest(Request $request, TelegramAlertService $telegramAlertService): RedirectResponse
    {
        $user = $request->user();

        if (! is_string($user?->telegram_chat_id) || trim($user->telegram_chat_id) === '') {
            return Redirect::route('profile.edit')->with('status', 'telegram-chat-id-required');
        }

        $telegramAlertService->sendUserTestMessage($user);
        $user->forceFill([
            'telegram_test_queued_at' => now(),
        ])->save();

        return Redirect::route('profile.edit')->with('status', 'profile-telegram-test-queued');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
