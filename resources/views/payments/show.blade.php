<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Payment Details</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-slate-900">{{ $payment->receipt_number }}</h3>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('payments.edit', $payment) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                <a href="{{ route('payments.refund.page', $payment) }}"
                    class="rounded-lg border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50">Refund</a>
                <a href="{{ route('payments.void.page', $payment) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Void</a>
                <a href="{{ route('payments.receipt', $payment) }}"
                    class="rounded-lg border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">Print
                    Receipt</a>
                <a href="{{ route('bookings.invoice', $booking) }}"
                    class="rounded-lg border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">Print
                    Invoice</a>
                <a href="{{ route('payments.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back</a>
            </div>
        </div>

        @if (session('status') === 'payment-created')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Payment created successfully.
            </p>
        @elseif (session('status') === 'payment-updated')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Payment updated successfully.
            </p>
        @elseif (session('status') === 'payment-refunded')
            <p class="mt-4 rounded-lg bg-amber-50 px-4 py-2 text-sm text-amber-700">Payment marked as refunded.</p>
        @elseif (session('status') === 'payment-voided')
            <p class="mt-4 rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-700">Payment marked as voided.</p>
        @endif

        <dl class="mt-6 grid gap-4 text-sm md:grid-cols-3">
            <div>
                <dt class="text-slate-500">Booking</dt>
                <dd class="font-medium text-slate-900">{{ $booking->booking_reference }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Guest</dt>
                <dd class="font-medium text-slate-900">{{ $booking->guest->full_name }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Payment Date</dt>
                <dd class="font-medium text-slate-900">{{ $payment->payment_date->format('Y-m-d') }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Amount</dt>
                <dd class="font-medium text-slate-900">RM {{ number_format((float) $payment->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Method</dt>
                <dd class="font-medium text-slate-900">{{ str_replace('_', ' ', $payment->payment_method) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Status</dt>
                <dd class="font-medium text-slate-900">{{ str_replace('_', ' ', $payment->payment_status) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Reference Number</dt>
                <dd class="font-medium text-slate-900">{{ $payment->reference_number ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Received By</dt>
                <dd class="font-medium text-slate-900">{{ $payment->receivedBy->name }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Notes</dt>
                <dd class="whitespace-pre-line font-medium text-slate-900">{{ $payment->notes ?: '-' }}</dd>
            </div>
        </dl>

        @php
            $bookingStatusClasses = [
                'unpaid' => 'bg-rose-100 text-rose-800',
                'partially_paid' => 'bg-amber-100 text-amber-800',
                'paid' => 'bg-emerald-100 text-emerald-800',
                'overpaid' => 'bg-blue-100 text-blue-800',
            ];

            $paymentSummaryStatus = $booking->paymentSummaryStatus();
        @endphp

        <div class="mt-6 grid gap-4 md:grid-cols-4">
            <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Booking Total</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">RM
                    {{ number_format((float) $booking->total_amount, 2) }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Paid</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">RM
                    {{ number_format($booking->totalPaidAmount(), 2) }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Outstanding</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">RM
                    {{ number_format($booking->outstandingBalanceAmount(), 2) }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Payment Summary</p>
                <p
                    class="mt-2 inline-flex rounded-full px-2.5 py-1 text-sm font-semibold {{ $bookingStatusClasses[$paymentSummaryStatus] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ str_replace('_', ' ', $paymentSummaryStatus) }}</p>
            </article>
        </div>

        <div class="mt-6">
            <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                onsubmit="return confirm('Delete this payment record?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="rounded-lg border border-rose-300 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Delete
                    Payment</button>
            </form>
        </div>
    </section>
</x-app-layout>
