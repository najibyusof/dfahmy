<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-900">Link Booking History</h2>
    </x-slot>

    <section class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-xs font-semibold uppercase text-emerald-700">Existing guest record found</p>
        <h3 class="mt-2 text-xl font-semibold text-slate-900">Confirm your booking details</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600">We found previous bookings for {{ $email }}. Enter the
            phone and identification number used for those bookings to securely link them to this account.</p>

        <form method="POST" action="{{ route('guest.history.link') }}" class="mt-6 space-y-4">
            @csrf
            <label class="block text-sm font-medium text-slate-700">Phone number
                <input name="phone_number" value="{{ old('phone_number') }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            </label>
            <label class="block text-sm font-medium text-slate-700">Identification number
                <input name="identification_number" value="{{ old('identification_number') }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            </label>
            <button type="submit"
                class="w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Link
                my booking history</button>
        </form>
    </section>
</x-app-layout>
