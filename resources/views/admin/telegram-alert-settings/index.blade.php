<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Telegram Alert Settings</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm text-slate-600">Enable or disable each Telegram alert type for homestay operations. The bot
            token stays in environment variables, and each internal user adds their own Telegram chat ID on the profile
            page.</p>

        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <h3 class="text-sm font-semibold text-amber-900">Admin setup guide</h3>
            <ol class="mt-3 space-y-3 pl-5 text-sm text-amber-950 list-decimal">
                <li>Open Telegram and search for <span class="font-semibold">@BotFather</span>.</li>
                <li>Create a bot with <span class="font-semibold">/newbot</span>, follow the prompts, and copy the bot
                    token that BotFather returns.</li>
                <li>Save that token in your server <span class="font-semibold">.env</span> file as <span
                        class="font-semibold">TELEGRAM_BOT_TOKEN=your_bot_token</span>.</li>
                <li>Ask each internal user who should receive alerts to open their <span
                        class="font-semibold">Profile</span>
                    page and save their own Telegram chat ID there.</li>
                <li>Each user can get their chat ID by sending a message to the bot, then opening <span
                        class="font-semibold">https://api.telegram.org/bot&lt;YOUR_BOT_TOKEN&gt;/getUpdates</span> and
                    copying the matching <span class="font-semibold">chat.id</span> value from their private chat or
                    target group.</li>
                <li>Users must not save the bot's own ID. Telegram will reject messages sent to the bot itself.</li>
                <li>Create a long random secret for health checks and save it as <span
                        class="font-semibold">HEALTH_CHECK_TOKEN=your_random_secret</span> in <span
                        class="font-semibold">.env</span>.</li>
                <li>After updating <span class="font-semibold">.env</span>, refresh configuration on the server with
                    <span class="font-semibold">php artisan optimize:clear</span> and use <span
                        class="font-semibold">Send Test Telegram</span> on this page to confirm delivery.
                </li>
            </ol>
        </div>

        @if (session('status') === 'telegram-alert-settings-saved')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Telegram alert settings saved.
            </p>
        @elseif (session('status') === 'telegram-test-alert-queued')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Test Telegram alert queued
                successfully.</p>
        @endif

        <form method="POST" action="{{ route('admin.telegram-alert-settings.test') }}" class="mt-4">
            @csrf
            <button type="submit"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Send
                Test Telegram</button>
        </form>

        <form method="POST" action="{{ route('admin.telegram-alert-settings.update') }}" class="mt-5 space-y-3">
            @csrf
            @method('PATCH')

            @php
                $labels = [
                    'telegram_new_booking' => 'New booking',
                    'telegram_booking_cancellation' => 'Booking cancellation',
                    'telegram_check_in' => 'Check-in',
                    'telegram_check_out' => 'Check-out',
                    'telegram_overdue_payment_outstanding_balance' => 'Overdue payment / outstanding balance',
                    'telegram_urgent_housekeeping_task' => 'Urgent housekeeping task',
                    'telegram_urgent_maintenance_request' => 'Urgent maintenance request',
                ];
            @endphp

            @foreach ($keys as $key)
                <label class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3">
                    <span class="text-sm font-medium text-slate-800">{{ $labels[$key] ?? $key }}</span>
                    <span class="inline-flex items-center gap-2">
                        <input type="hidden" name="{{ $key }}" value="0">
                        <input type="checkbox" name="{{ $key }}" value="1" @checked((bool) ($states[$key] ?? false))
                            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    </span>
                </label>
                @error($key)
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
            @endforeach

            <button type="submit"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save
                Settings</button>
        </form>
    </section>
</x-app-layout>
