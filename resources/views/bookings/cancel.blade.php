<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Cancel Booking</h2>
    </x-slot>

    <section class="max-w-2xl rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">{{ $booking->booking_reference }}</h3>
        <p class="mt-2 text-sm text-slate-600">This action sets booking status to cancelled. Cancelled bookings do not
            block room availability.</p>

        <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="mt-6 flex items-center gap-3">
            @csrf
            @method('PATCH')
            <button type="submit"
                class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">Confirm
                Cancel</button>
            <a href="{{ route('bookings.show', $booking) }}"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back</a>
        </form>
    </section>
</x-app-layout>
