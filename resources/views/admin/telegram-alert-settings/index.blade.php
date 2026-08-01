<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Telegram Alert Settings</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm text-slate-600">Enable or disable each Telegram alert type for homestay operations. Bot token
            and chat ID stay in environment variables only.</p>

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
