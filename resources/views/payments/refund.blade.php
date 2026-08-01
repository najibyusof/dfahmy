<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Refund Payment</h2>
    </x-slot>

    <section class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">{{ $payment->receipt_number }}</h3>
        <p class="mt-1 text-sm text-slate-600">
            Booking {{ $payment->booking->booking_reference }} - {{ $payment->booking->guest->full_name }}
        </p>

        <p class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
            This action marks the payment as refunded and excludes it from total paid calculations.
        </p>

        <form method="POST" action="{{ route('payments.refund', $payment) }}" class="mt-5 space-y-3">
            @csrf
            @method('PATCH')

            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700">Refund Notes</label>
                <textarea id="notes" name="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">Confirm
                    Refund</button>
                <a href="{{ route('payments.show', $payment) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back</a>
            </div>
        </form>
    </section>
</x-app-layout>
