<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Bookings</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('bookings.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search ref, guest, room"
                    class="rounded-lg border-slate-300 text-sm">
                <select name="booking_status" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['booking_status'] === $status)>
                            {{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
                <select name="payment_summary" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Payment States</option>
                    <option value="unpaid" @selected(($filters['payment_summary'] ?? '') === 'unpaid')>Unpaid</option>
                    <option value="partially_paid" @selected(($filters['payment_summary'] ?? '') === 'partially_paid')>Partially Paid</option>
                    <option value="paid" @selected(($filters['payment_summary'] ?? '') === 'paid')>Paid</option>
                    <option value="overpaid" @selected(($filters['payment_summary'] ?? '') === 'overpaid')>Overpaid</option>
                </select>
                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Filter</button>
                <a href="{{ route('bookings.index') }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Reset</a>
            </form>

            <a href="{{ route('bookings.create') }}"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">New
                Booking</a>
        </div>

        @php($quick = $filters['quick'] ?? '')

        <div class="mt-4 inline-flex flex-wrap rounded-lg border border-slate-300 p-1 text-xs font-semibold">
            <a href="{{ route('bookings.index', array_merge(request()->except('page'), ['quick' => ''])) }}"
                class="rounded-md px-3 py-1 {{ $quick === '' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">All
                <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $quick === '' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $quickFilterCounts['all'] ?? 0 }}</span></a>
            <a href="{{ route('bookings.index', array_merge(request()->except('page'), ['quick' => 'unpaid'])) }}"
                class="rounded-md px-3 py-1 {{ $quick === 'unpaid' ? 'bg-rose-700 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">Unpaid
                <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $quick === 'unpaid' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700' }}">{{ $quickFilterCounts['unpaid'] ?? 0 }}</span></a>
            <a href="{{ route('bookings.index', array_merge(request()->except('page'), ['quick' => 'partially_paid'])) }}"
                class="rounded-md px-3 py-1 {{ $quick === 'partially_paid' ? 'bg-amber-700 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">Partially
                Paid <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $quick === 'partially_paid' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-700' }}">{{ $quickFilterCounts['partially_paid'] ?? 0 }}</span></a>
            <a href="{{ route('bookings.index', array_merge(request()->except('page'), ['quick' => 'today'])) }}"
                class="rounded-md px-3 py-1 {{ $quick === 'today' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Today
                <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $quick === 'today' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $quickFilterCounts['today'] ?? 0 }}</span></a>
            <a href="{{ route('bookings.index', array_merge(request()->except('page'), ['quick' => 'upcoming'])) }}"
                class="rounded-md px-3 py-1 {{ $quick === 'upcoming' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Upcoming
                <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $quick === 'upcoming' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $quickFilterCounts['upcoming'] ?? 0 }}</span></a>
            <a href="{{ route('bookings.index', array_merge(request()->except('page'), ['quick' => 'checked_in'])) }}"
                class="rounded-md px-3 py-1 {{ $quick === 'checked_in' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Checked
                In <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $quick === 'checked_in' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $quickFilterCounts['checked_in'] ?? 0 }}</span></a>
            <a href="{{ route('bookings.index', array_merge(request()->except('page'), ['quick' => 'checked_out'])) }}"
                class="rounded-md px-3 py-1 {{ $quick === 'checked_out' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Checked
                Out <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $quick === 'checked_out' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $quickFilterCounts['checked_out'] ?? 0 }}</span></a>
        </div>
        <p class="mt-2 text-xs text-slate-500">Today and Upcoming are based on booking check-in date. Today = check-in
            date is today; Upcoming = check-in date is after today.</p>

        @if (session('status') === 'booking-created')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Booking created successfully.
            </p>
        @elseif (session('status') === 'booking-updated')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Booking updated successfully.
            </p>
        @elseif (session('status') === 'booking-deleted')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Booking deleted successfully.
            </p>
        @elseif (session('status') === 'booking-cancelled')
            <p class="mt-4 rounded-lg bg-amber-50 px-4 py-2 text-sm text-amber-700">Booking cancelled successfully.</p>
        @elseif (session('status') === 'booking-checked-in')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Booking checked in successfully.
            </p>
        @elseif (session('status') === 'booking-checked-out')
            <p class="mt-4 rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-700">Booking checked out successfully.
            </p>
        @endif

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Guest</th>
                        <th class="px-4 py-3">Rooms</th>
                        <th class="px-4 py-3">Check In</th>
                        <th class="px-4 py-3">Check Out</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Payment</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($bookings as $booking)
                        @php($firstRoom = $booking->bookingRoomItems->first()?->room)
                        @php($paymentSummaryStatus = $booking->paymentSummaryStatus())
                        @php(
    $paymentSummaryStatusClasses = [
        'unpaid' => 'border border-rose-200 bg-rose-50 text-rose-700',
        'partially_paid' => 'border border-amber-200 bg-amber-50 text-amber-700',
        'paid' => 'border border-emerald-200 bg-emerald-50 text-emerald-700',
        'overpaid' => 'border border-blue-200 bg-blue-50 text-blue-700'
    ]
)
                        @php(
    $paymentSummaryActiveRingClasses = [
        'unpaid' => 'ring-1 ring-rose-300',
        'partially_paid' => 'ring-1 ring-amber-300',
        'paid' => 'ring-1 ring-emerald-300',
        'overpaid' => 'ring-1 ring-blue-300'
    ]
)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $booking->booking_reference }}</td>
                            <td class="px-4 py-3">{{ $booking->guest->full_name }}</td>
                            <td class="px-4 py-3">
                                {{ $booking->bookingRoomItems->count() }} room(s)
                                @if ($firstRoom)
                                    <span class="text-xs text-slate-500">· {{ $firstRoom->code }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $booking->check_in_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ $booking->check_out_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses[$booking->booking_status] ?? 'bg-slate-100 text-slate-800' }}">
                                    {{ str_replace('_', ' ', $booking->booking_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('bookings.index', array_merge(request()->except('page'), ['payment_summary' => $paymentSummaryStatus])) }}"
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold transition {{ $paymentSummaryStatusClasses[$paymentSummaryStatus] ?? 'border border-slate-200 bg-slate-50 text-slate-700' }} {{ ($filters['payment_summary'] ?? '') === $paymentSummaryStatus ? $paymentSummaryActiveRingClasses[$paymentSummaryStatus] ?? 'ring-1 ring-slate-300' : 'hover:opacity-90' }}">
                                    {{ str_replace('_', ' ', $paymentSummaryStatus) }}
                                </a>
                            </td>
                            <td class="px-4 py-3">RM {{ number_format((float) $booking->total_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('bookings.show', $booking) }}"
                                        class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">View</a>
                                    <a href="{{ route('bookings.edit', $booking) }}"
                                        class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                                    <a href="{{ route('bookings.cancel.page', $booking) }}"
                                        class="rounded-lg border border-amber-300 px-3 py-1 text-xs font-semibold text-amber-800 hover:bg-amber-50">Cancel</a>
                                    <a href="{{ route('bookings.check-in.page', $booking) }}"
                                        class="rounded-lg border border-emerald-300 px-3 py-1 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">Check
                                        In</a>
                                    <a href="{{ route('bookings.check-out.page', $booking) }}"
                                        class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Check
                                        Out</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-slate-500">No bookings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $bookings->links() }}</div>
    </section>
</x-app-layout>
