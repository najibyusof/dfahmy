<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Payment Receipt</h2>
    </x-slot>

    <section class="mb-4 flex items-center justify-between print:hidden">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Receipt {{ $payment->receipt_number }}</h3>
            <p class="text-sm text-slate-600">Booking {{ $booking->booking_reference }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Print
                Receipt</button>
            <a href="{{ route('payments.show', $payment) }}"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back</a>
        </div>
    </section>

    <section class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm print:shadow-none">
        <div class="border-b border-slate-200 pb-5 text-center">
            <h4 class="text-2xl font-semibold tracking-tight text-slate-900">DFahMy Eco Resort</h4>
            <p class="mt-2 text-sm text-slate-600">Official Payment Receipt</p>
        </div>

        <div class="mt-6 grid gap-4 text-sm md:grid-cols-2">
            <div>
                <p class="text-slate-500">Received From</p>
                <p class="font-semibold text-slate-900">{{ $booking->guest->full_name }}</p>
                <p class="text-slate-700">{{ $booking->guest->email }}</p>
            </div>
            <div class="md:text-right">
                <p><span class="text-slate-500">Receipt Number:</span> <span
                        class="font-semibold text-slate-900">{{ $payment->receipt_number }}</span></p>
                <p><span class="text-slate-500">Payment Date:</span> <span
                        class="font-semibold text-slate-900">{{ $payment->payment_date->format('Y-m-d') }}</span></p>
                <p><span class="text-slate-500">Booking Ref:</span> <span
                        class="font-semibold text-slate-900">{{ $booking->booking_reference }}</span></p>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-5">
            <div class="flex items-center justify-between text-sm"><span class="text-slate-600">Amount
                    Received</span><span class="text-lg font-semibold text-slate-900">RM
                    {{ number_format((float) $payment->amount, 2) }}</span></div>
            <div class="mt-2 flex items-center justify-between text-sm"><span class="text-slate-600">Payment
                    Method</span><span
                    class="font-medium text-slate-900">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-sm"><span class="text-slate-600">Reference
                    Number</span><span
                    class="font-medium text-slate-900">{{ $payment->reference_number ?: '-' }}</span></div>
            <div class="mt-2 flex items-center justify-between text-sm"><span class="text-slate-600">Payment
                    Status</span><span
                    class="font-medium text-slate-900">{{ str_replace('_', ' ', $payment->payment_status) }}</span>
            </div>
        </div>

        <div class="mt-6 text-sm">
            <p class="text-slate-500">Received By</p>
            <p class="font-semibold text-slate-900">{{ $payment->receivedBy->name }}</p>
        </div>

        <div class="mt-6 text-sm">
            <p class="text-slate-500">Notes</p>
            <p class="mt-1 whitespace-pre-line text-slate-800">{{ $payment->notes ?: '-' }}</p>
        </div>
    </section>
</x-app-layout>
