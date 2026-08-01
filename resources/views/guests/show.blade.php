<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Guest Profile</h2>
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ $guest->full_name }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ $guest->email }} · {{ $guest->phone_number }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('bookings.create', ['guest' => $guest->id]) }}"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">New
                        Booking</a>
                    <a href="{{ route('guests.edit', $guest) }}"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                    <a href="{{ route('guests.index') }}"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back</a>
                </div>
            </div>

            <dl class="mt-6 grid gap-4 text-sm md:grid-cols-2">
                <div>
                    <dt class="text-slate-500">Identification / Passport</dt>
                    <dd class="font-medium text-slate-900">{{ $guest->identification_number }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Nationality</dt>
                    <dd class="font-medium text-slate-900">{{ $guest->nationality ?: '-' }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-slate-500">Address</dt>
                    <dd class="font-medium text-slate-900">{{ $guest->address ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Emergency Contact Name</dt>
                    <dd class="font-medium text-slate-900">{{ $guest->emergency_contact_name ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Emergency Contact Phone</dt>
                    <dd class="font-medium text-slate-900">{{ $guest->emergency_contact_phone ?: '-' }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-slate-500">Notes</dt>
                    <dd class="font-medium text-slate-900">{{ $guest->notes ?: '-' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Upcoming Bookings</h3>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Reference</th>
                            <th class="px-3 py-2">Room</th>
                            <th class="px-3 py-2">Check In</th>
                            <th class="px-3 py-2">Check Out</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($upcomingBookings as $booking)
                            @php($firstRoom = $booking->bookingRoomItems->first()?->room)
                            <tr>
                                <td class="px-3 py-2 font-medium text-slate-900">{{ $booking->booking_reference }}</td>
                                <td class="px-3 py-2">{{ $firstRoom?->code ?? ($firstRoom?->name ?? '-') }}</td>
                                <td class="px-3 py-2">{{ $booking->check_in_date->format('Y-m-d') }}</td>
                                <td class="px-3 py-2">{{ $booking->check_out_date->format('Y-m-d') }}</td>
                                <td class="px-3 py-2">{{ str_replace('_', ' ', $booking->booking_status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-slate-500">No upcoming bookings.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Past Bookings</h3>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Reference</th>
                            <th class="px-3 py-2">Room</th>
                            <th class="px-3 py-2">Check In</th>
                            <th class="px-3 py-2">Check Out</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($pastBookings as $booking)
                            @php($firstRoom = $booking->bookingRoomItems->first()?->room)
                            <tr>
                                <td class="px-3 py-2 font-medium text-slate-900">{{ $booking->booking_reference }}</td>
                                <td class="px-3 py-2">{{ $firstRoom?->code ?? ($firstRoom?->name ?? '-') }}</td>
                                <td class="px-3 py-2">{{ $booking->check_in_date->format('Y-m-d') }}</td>
                                <td class="px-3 py-2">{{ $booking->check_out_date->format('Y-m-d') }}</td>
                                <td class="px-3 py-2">{{ str_replace('_', ' ', $booking->booking_status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-slate-500">No past bookings.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
