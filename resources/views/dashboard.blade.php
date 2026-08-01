<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Home Dashboard</h2>
    </x-slot>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Today's Arrivals</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['todays_arrivals'] }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Today's Departures</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['todays_departures'] }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Current Guests</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $stats['current_guests'] }}</p>
        </article>
        <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-emerald-700">Available Rooms</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ $stats['available_rooms'] }}</p>
        </article>
        <article class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-rose-700">Occupied Rooms</p>
            <p class="mt-2 text-3xl font-semibold text-rose-900">{{ $stats['occupied_rooms'] }}</p>
        </article>
        <article class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-sky-700">Rooms Needing Cleaning</p>
            <p class="mt-2 text-3xl font-semibold text-sky-900">{{ $stats['rooms_needing_cleaning'] }}</p>
        </article>
        @can('maintenance.manage')
            <article class="rounded-2xl border border-orange-200 bg-orange-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase text-orange-700">Rooms Under Maintenance</p>
                <p class="mt-2 text-3xl font-semibold text-orange-900">{{ $stats['rooms_under_maintenance'] }}</p>
            </article>
        @endcan
        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-amber-700">Expected Payments Today</p>
            <p class="mt-2 text-3xl font-semibold text-amber-900">RM
                {{ number_format((float) $stats['expected_payments_today'], 2) }}</p>
        </article>
        <article class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-rose-700">Outstanding Balances</p>
            <p class="mt-2 text-3xl font-semibold text-rose-900">RM
                {{ number_format((float) $stats['outstanding_balances'], 2) }}</p>
        </article>
        <article class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-rose-700">Unpaid Bookings</p>
            <p class="mt-2 text-3xl font-semibold text-rose-900">{{ $stats['unpaid_bookings'] }}</p>
        </article>
        <article class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-amber-700">Partially Paid Bookings</p>
            <p class="mt-2 text-3xl font-semibold text-amber-900">{{ $stats['partially_paid_bookings'] }}</p>
        </article>
    </section>

    <section class="mt-6 grid gap-5 lg:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Recent Bookings</h3>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Ref</th>
                            <th class="px-3 py-2">Guest</th>
                            <th class="px-3 py-2">Check-In</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($stats['recent_bookings'] as $booking)
                            <tr>
                                <td class="px-3 py-2">{{ $booking->booking_reference }}</td>
                                <td class="px-3 py-2">{{ $booking->guest?->full_name }}</td>
                                <td class="px-3 py-2">{{ $booking->check_in_date?->format('Y-m-d') }}</td>
                                <td class="px-3 py-2">{{ str_replace('_', ' ', $booking->booking_status) }}</td>
                        </tr>@empty<tr>
                                <td colspan="4" class="px-3 py-3 text-slate-500">No recent bookings.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Urgent Tasks</h3>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Room</th>
                            <th class="px-3 py-2">Type</th>
                            <th class="px-3 py-2">Due</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($stats['urgent_tasks'] as $task)
                            <tr>
                                <td class="px-3 py-2">{{ $task->room_label }}</td>
                                <td class="px-3 py-2">{{ str_replace('_', ' ', $task->task_type) }}</td>
                                <td class="px-3 py-2">{{ $task->due_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-3 py-2">{{ str_replace('_', ' ', $task->status) }}</td>
                        </tr>@empty<tr>
                                <td colspan="4" class="px-3 py-3 text-slate-500">No urgent tasks.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</x-app-layout>
