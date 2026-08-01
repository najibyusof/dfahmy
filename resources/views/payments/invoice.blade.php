<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Invoice</h2>
    </x-slot>

    <section class="mb-4 flex items-center justify-between print:hidden">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Booking Invoice</h3>
            <p class="text-sm text-slate-600">{{ $booking->booking_reference }} - {{ $booking->guest->full_name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Print
                Invoice</button>
            <a href="{{ route('bookings.show', $booking) }}"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back</a>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm print:shadow-none">
        <div class="flex items-start justify-between gap-6 border-b border-slate-200 pb-6">
            <div>
                <h4 class="text-2xl font-semibold tracking-tight text-slate-900">DFahMy Eco Resort</h4>
                <p class="mt-2 text-sm text-slate-600">Homestay Booking Invoice</p>
                <p class="text-sm text-slate-600">Issued: {{ now()->format('Y-m-d') }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-slate-500">Invoice No.</p>
                <p class="text-lg font-semibold text-slate-900">INV-{{ $booking->booking_reference }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 text-sm md:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bill To</p>
                <p class="mt-2 font-semibold text-slate-900">{{ $booking->guest->full_name }}</p>
                <p class="text-slate-700">{{ $booking->guest->email }}</p>
                <p class="text-slate-700">{{ $booking->guest->phone_number }}</p>
            </div>
            <div class="md:text-right">
                <p><span class="text-slate-500">Booking Ref:</span> <span
                        class="font-semibold text-slate-900">{{ $booking->booking_reference }}</span></p>
                <p><span class="text-slate-500">Check In:</span> <span
                        class="font-semibold text-slate-900">{{ $booking->check_in_date->format('Y-m-d') }}</span></p>
                <p><span class="text-slate-500">Check Out:</span> <span
                        class="font-semibold text-slate-900">{{ $booking->check_out_date->format('Y-m-d') }}</span></p>
                <p><span class="text-slate-500">Payment Status:</span> <span
                        class="font-semibold text-slate-900">{{ str_replace('_', ' ', $paymentSummaryStatus) }}</span>
                </p>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Room</th>
                        <th class="px-3 py-2">Period</th>
                        <th class="px-3 py-2">Nightly Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($booking->bookingRoomItems as $item)
                        <tr>
                            <td class="px-3 py-2">{{ $item->room->code }} - {{ $item->room->name }}</td>
                            <td class="px-3 py-2">{{ $item->check_in_date->format('Y-m-d') }} to
                                {{ $item->check_out_date->format('Y-m-d') }}</td>
                            <td class="px-3 py-2">RM {{ number_format((float) $item->nightly_rate, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 overflow-x-auto">
            <h5 class="mb-2 text-sm font-semibold text-slate-900">Payment History</h5>
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Receipt</th>
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2">Method</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Amount</th>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-3 text-center text-slate-500">No payments recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8 ml-auto max-w-sm space-y-2 text-sm">
            <div class="flex items-center justify-between"><span class="text-slate-600">Subtotal</span><span
                    class="font-medium text-slate-900">RM {{ number_format((float) $booking->subtotal, 2) }}</span>
            </div>
            <div class="flex items-center justify-between"><span class="text-slate-600">Discount</span><span
                    class="font-medium text-slate-900">RM {{ number_format((float) $booking->discount, 2) }}</span>
            </div>
            <div class="flex items-center justify-between"><span class="text-slate-600">Tax</span><span
                    class="font-medium text-slate-900">RM {{ number_format((float) $booking->tax, 2) }}</span></div>
            <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-base"><span
                    class="font-semibold text-slate-900">Total</span><span class="font-semibold text-slate-900">RM
                    {{ number_format((float) $booking->total_amount, 2) }}</span></div>
            <div class="flex items-center justify-between"><span class="text-slate-600">Total Paid</span><span
                    class="font-medium text-slate-900">RM {{ number_format($totalPaid, 2) }}</span></div>
            <div class="flex items-center justify-between"><span class="text-slate-600">Outstanding</span><span
                    class="font-semibold text-rose-700">RM {{ number_format($outstanding, 2) }}</span></div>
        </div>
    </section>
</x-app-layout>
