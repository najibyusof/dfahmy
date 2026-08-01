<?php

namespace App\Http\Controllers;

use App\Models\OperationAlertSetting;
use App\Services\TelegramAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminTelegramAlertSettingController extends Controller
{
    public function index(Request $request, TelegramAlertService $telegramAlertService): View
    {
        $this->ensureSuperAdmin($request);

        return view('admin.telegram-alert-settings.index', [
            'keys' => OperationAlertSetting::TELEGRAM_KEYS,
            'states' => $telegramAlertService->allToggleStates(),
        ]);
    }

    public function update(Request $request, TelegramAlertService $telegramAlertService): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        $rules = [];
        foreach (OperationAlertSetting::TELEGRAM_KEYS as $key) {
            $rules[$key] = ['required', 'boolean'];
        }

        /** @var array<string, bool|int|string> $validated */
        $validated = $request->validate($rules);

        $toggles = [];
        foreach (OperationAlertSetting::TELEGRAM_KEYS as $key) {
            $toggles[$key] = (bool) $validated[$key];
        }

        $telegramAlertService->saveToggleStates($toggles);

        return redirect()->route('admin.telegram-alert-settings.index')->with('status', 'telegram-alert-settings-saved');
    }

    public function sendTest(Request $request, TelegramAlertService $telegramAlertService): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        $telegramAlertService->sendTestMessage((string) $request->user()->name);

        return redirect()->route('admin.telegram-alert-settings.index')->with('status', 'telegram-test-alert-queued');
    }

    private function ensureSuperAdmin(Request $request): void
    {
        if (! $request->user()?->hasRole('Super Admin')) {
            abort(403);
        }
    }
}
