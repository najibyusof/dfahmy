<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Payments</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('payments.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ $filters['search'] }}"
                    placeholder="Search receipt, booking, guest" class="rounded-lg border-slate-300 text-sm">

                <select name="payment_status" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Statuses</option>
                    @foreach ($paymentStatuses as $paymentStatus)
                        <option value="{{ $paymentStatus }}" @selected($filters['payment_status'] === $paymentStatus)>
                            {{ str_replace('_', ' ', $paymentStatus) }}</option>
                    @endforeach
                </select>

                <select name="payment_method" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Methods</option>
                    @foreach ($paymentMethods as $paymentMethod)
                        <option value="{{ $paymentMethod }}" @selected($filters['payment_method'] === $paymentMethod)>
                            {{ str_replace('_', ' ', $paymentMethod) }}</option>
                    @endforeach
                </select>

                <select name="booking_id" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Bookings</option>
                    @foreach ($bookings as $booking)
                        <option value="{{ $booking->id }}" @selected($filters['booking_id'] === (string) $booking->id)>
                            {{ $booking->booking_reference }}</option>
                    @endforeach
                </select>

                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Filter</button>
                <a href="{{ route('payments.index') }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Reset</a>
            </form>

            <a href="{{ route('payments.create') }}"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Record
                Payment</a>
        </div>

        @if (session('status') === 'payment-deleted')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Payment deleted successfully.
            </p>
        @endif

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Receipt</th>
                        <th class="px-3 py-2">Booking</th>
                        <th class="px-3 py-2">Payment Date</th>
                        <th class="px-3 py-2">Amount</th>
                        <th class="px-3 py-2">Method</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Received By</th>
                        <th class="px-3 py-2">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-3 py-2 font-medium text-slate-900">{{ $payment->receipt_number }}</td>
                            <td class="px-3 py-2">
                                <div>{{ $payment->booking->booking_reference }}</div>
                                <div class="text-xs text-slate-500">{{ $payment->booking->guest->full_name }}</div>
                            </td>
                            <td class="px-3 py-2">{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td class="px-3 py-2">RM {{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="px-3 py-2">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                            <td class="px-3 py-2">
                                @php
                                    $statusBadgeClasses = [
                                        'pending' => 'border border-amber-200 bg-amber-50 text-amber-700',
                                        'paid' => 'border border-emerald-200 bg-emerald-50 text-emerald-700',
                                        'refunded' => 'border border-rose-200 bg-rose-50 text-rose-700',
                                        'voided' => 'border border-slate-300 bg-slate-100 text-slate-700',
                                    ];
                                @endphp
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses[$payment->payment_status] ?? 'border border-slate-200 bg-slate-50 text-slate-700' }}">{{ str_replace('_', ' ', $payment->payment_status) }}</span>
                            </td>
                            <td class="px-3 py-2">{{ $payment->receivedBy->name }}</td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('payments.show', $payment) }}"
                                        class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">View</a>
                                    <a href="{{ route('payments.edit', $payment) }}"
                                        class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                                    <a href="{{ route('payments.receipt', $payment) }}"
                                        class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Receipt</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-slate-500">No payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $payments->links() }}</div>
    </section>
</x-app-layout>
