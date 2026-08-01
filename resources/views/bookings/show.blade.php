<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Booking Details</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-lg font-semibold text-slate-900">{{ $booking->booking_reference }}</h3>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('bookings.edit', $booking) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                @can('payments.manage')
                    <a href="{{ route('payments.create', ['booking' => $booking->id]) }}"
                        class="rounded-lg border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">Record
                        Payment</a>
                    <a href="{{ route('bookings.invoice', $booking) }}"
                        class="rounded-lg border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">Invoice</a>
                @endcan
                <a href="{{ route('bookings.cancel.page', $booking) }}"
                    class="rounded-lg border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50">Cancel</a>
                <a href="{{ route('bookings.check-in.page', $booking) }}"
                    class="rounded-lg border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">Check
                    In</a>
                <a href="{{ route('bookings.check-out.page', $booking) }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Check
                    Out</a>
                <a href="{{ route('bookings.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back</a>
            </div>
        </div>

        <dl class="mt-6 grid gap-4 text-sm md:grid-cols-3">
            <div>
                <dt class="text-slate-500">Guest</dt>
                <dd class="font-medium text-slate-900">{{ $booking->guest->full_name }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Booking Source</dt>
                <dd class="font-medium text-slate-900">{{ $booking->booking_source }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Check In Date</dt>
                <dd class="font-medium text-slate-900">{{ $booking->check_in_date->format('Y-m-d') }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Check Out Date</dt>
                <dd class="font-medium text-slate-900">{{ $booking->check_out_date->format('Y-m-d') }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Status</dt>
                <dd class="font-medium text-slate-900">{{ str_replace('_', ' ', $booking->booking_status) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Adults</dt>
                <dd class="font-medium text-slate-900">{{ $booking->adults }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Children</dt>
                <dd class="font-medium text-slate-900">{{ $booking->children }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Subtotal</dt>
                <dd class="font-medium text-slate-900">RM {{ number_format((float) $booking->subtotal, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Discount</dt>
                <dd class="font-medium text-slate-900">RM {{ number_format((float) $booking->discount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Tax</dt>
                <dd class="font-medium text-slate-900">RM {{ number_format((float) $booking->tax, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Total Amount</dt>
                <dd class="font-medium text-slate-900">RM {{ number_format((float) $booking->total_amount, 2) }}</dd>
            </div>
            <div class="md:col-span-3">
                <dt class="text-slate-500">Special Requests</dt>
                <dd class="font-medium text-slate-900">{{ $booking->special_requests ?: '-' }}</dd>
            </div>
            <div class="md:col-span-3">
                <dt class="text-slate-500">Internal Notes</dt>
                <dd class="font-medium text-slate-900">{{ $booking->internal_notes ?: '-' }}</dd>
            </div>
        </dl>

        @php
            $paymentSummaryStatus = $booking->paymentSummaryStatus();
            $paymentSummaryStatusClasses = [
                'unpaid' => 'bg-rose-100 text-rose-800',
                'partially_paid' => 'bg-amber-100 text-amber-800',
                'paid' => 'bg-emerald-100 text-emerald-800',
                'overpaid' => 'bg-blue-100 text-blue-800',
            ];
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
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Payment Status</p>
                <p
                    class="mt-2 inline-flex rounded-full px-2.5 py-1 text-sm font-semibold {{ $paymentSummaryStatusClasses[$paymentSummaryStatus] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ str_replace('_', ' ', $paymentSummaryStatus) }}</p>
            </article>
        </div>

        @can('payments.manage')
            <div class="mt-6 overflow-x-auto">
                <h4 class="mb-2 text-sm font-semibold text-slate-900">Payments</h4>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Receipt</th>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Method</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Amount</th>
                            <th class="px-3 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($booking->payments as $payment)
                            <tr>
                                <td class="px-3 py-2">{{ $payment->receipt_number }}</td>
                                <td class="px-3 py-2">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                <td class="px-3 py-2">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                <td class="px-3 py-2">{{ str_replace('_', ' ', $payment->payment_status) }}</td>
                                <td class="px-3 py-2">RM {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="px-3 py-2"><a href="{{ route('payments.show', $payment) }}"
                                        class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-3 text-center text-slate-500">No payments recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endcan

        <div class="mt-6 overflow-x-auto">
            <h4 class="mb-2 text-sm font-semibold text-slate-900">Room Items</h4>
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Room</th>
                        <th class="px-3 py-2">Nightly Rate</th>
                        <th class="px-3 py-2">Adults</th>
                        <th class="px-3 py-2">Children</th>
                        <th class="px-3 py-2">Check In</th>
                        <th class="px-3 py-2">Check Out</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($booking->bookingRoomItems as $item)
                        <tr>
                            <td class="px-3 py-2">{{ $item->room->code }} - {{ $item->room->name }}</td>
                            <td class="px-3 py-2">RM {{ number_format((float) $item->nightly_rate, 2) }}</td>
                            <td class="px-3 py-2">{{ $item->adults }}</td>
                            <td class="px-3 py-2">{{ $item->children }}</td>
                            <td class="px-3 py-2">{{ $item->check_in_date->format('Y-m-d') }}</td>
                            <td class="px-3 py-2">{{ $item->check_out_date->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
