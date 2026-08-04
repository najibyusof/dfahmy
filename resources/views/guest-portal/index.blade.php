<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-900">Book a Stay</h2>
    </x-slot>

    @if (session('guest_booking_status') === 'booking-requested')
        <div
            class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            Your booking request has been submitted. Our team will confirm it shortly.
        </div>
    @endif

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase text-emerald-700">Next 14 days</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-900">Available dates</h3>
            </div>
            <p class="text-xs text-slate-500">Availability shown for a one-night stay and one guest.</p>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-7">
            @foreach ($availabilityDays as $day)
                <a href="{{ route('guest.portal', ['check_in_date' => $day['date']->toDateString(), 'check_out_date' => $day['date']->copy()->addDay()->toDateString(), 'adults' => 1, 'children' => 0]) }}"
                    class="rounded-lg border p-3 transition {{ $day['available_count'] > 0 ? 'border-emerald-200 bg-emerald-50 hover:border-emerald-400' : 'pointer-events-none border-slate-200 bg-slate-50 text-slate-400' }}">
                    <span class="block text-xs font-medium">{{ $day['date']->format('D') }}</span>
                    <span class="mt-1 block text-lg font-semibold">{{ $day['date']->format('j M') }}</span>
                    <span
                        class="mt-1 block text-[11px] {{ $day['available_count'] > 0 ? 'text-emerald-700' : 'text-slate-400' }}">
                        {{ $day['available_count'] > 0 ? $day['available_count'] . ' room(s)' : 'Fully booked' }}
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h3 class="text-lg font-semibold text-slate-900">Find your room</h3>
        <form method="GET" action="{{ route('guest.portal') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <label class="text-xs font-medium text-slate-700">Check in
                <input type="date" name="check_in_date" min="{{ now()->toDateString() }}"
                    value="{{ $filters['check_in_date'] }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"
                    required>
            </label>
            <label class="text-xs font-medium text-slate-700">Check out
                <input type="date" name="check_out_date" min="{{ now()->addDay()->toDateString() }}"
                    value="{{ $filters['check_out_date'] }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"
                    required>
            </label>
            <label class="text-xs font-medium text-slate-700">Adults
                <input type="number" name="adults" min="1" max="50" value="{{ $filters['adults'] }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            </label>
            <label class="text-xs font-medium text-slate-700">Children
                <input type="number" name="children" min="0" max="50" value="{{ $filters['children'] }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            </label>
            <button type="submit"
                class="self-end rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Check
                availability</button>
        </form>

        @if ($availableRooms->isNotEmpty())
            @php($nights = \Carbon\Carbon::parse($filters['check_in_date'])->diffInDays(\Carbon\Carbon::parse($filters['check_out_date'])))
            <form method="POST" action="{{ route('guest.bookings.store') }}" class="mt-6">
                @csrf
                <input type="hidden" name="check_in_date" value="{{ $filters['check_in_date'] }}">
                <input type="hidden" name="check_out_date" value="{{ $filters['check_out_date'] }}">
                <input type="hidden" name="adults" value="{{ $filters['adults'] }}">
                <input type="hidden" name="children" value="{{ $filters['children'] }}">

                <fieldset>
                    <legend class="text-sm font-semibold text-slate-900">Choose a room</legend>
                    <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($availableRooms as $room)
                            <div>
                                <input id="room-{{ $room->id }}" type="radio" name="room_id"
                                    value="{{ $room->id }}" class="peer sr-only" @checked((string) old('room_id') === (string) $room->id)
                                    required>
                                <label for="room-{{ $room->id }}"
                                    class="block cursor-pointer rounded-lg border border-slate-200 p-4 transition hover:border-emerald-300 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:ring-1 peer-checked:ring-emerald-600">
                                    <span class="flex items-start justify-between gap-3">
                                        <span>
                                            <span class="block font-semibold text-slate-900">{{ $room->name }}</span>
                                            <span class="mt-1 block text-xs text-slate-500">{{ $room->code }} ·
                                                {{ $room->building?->name }}</span>
                                        </span>
                                        <span
                                            class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Available</span>
                                    </span>
                                    <span class="mt-4 block text-sm text-slate-600">Up to {{ $room->maximum_guests }}
                                        guests</span>
                                    <span class="mt-1 block text-lg font-semibold text-slate-900">RM
                                        {{ number_format((float) $room->base_nightly_rate * $nights, 2) }}</span>
                                    <span class="block text-xs text-slate-500">{{ $nights }} night(s), before any
                                        additional charges</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    <label class="block text-xs font-medium text-slate-700">Phone number
                        <input name="phone_number" value="{{ old('phone_number', $guest->phone_number) }}"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
                    </label>
                    <label class="block text-xs font-medium text-slate-700">Identification number
                        <input name="identification_number"
                            value="{{ old('identification_number', $guest->identification_number) }}"
                            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
                    </label>
                    <label class="block text-xs font-medium text-slate-700 md:col-span-2">Special requests
                        <textarea name="special_requests" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('special_requests') }}</textarea>
                    </label>
                </div>
                <button type="submit"
                    class="mt-4 w-full rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800 sm:w-auto">Request
                    selected room</button>
            </form>
        @else
            <p class="mt-6 text-sm text-slate-500">No rooms are available for these dates and guest count. Try another
                date range.</p>
        @endif
    </section>

    <section id="my-bookings" class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h3 class="text-lg font-semibold text-slate-900">My bookings</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Reference</th>
                        <th class="px-3 py-2">Room</th>
                        <th class="px-3 py-2">Stay</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="px-3 py-3 font-medium text-slate-900">{{ $booking->booking_reference }}</td>
                            <td class="px-3 py-3">{{ $booking->bookingRoomItems->first()?->room?->name ?? '-' }}</td>
                            <td class="px-3 py-3">{{ $booking->check_in_date->format('Y-m-d') }} to
                                {{ $booking->check_out_date->format('Y-m-d') }}</td>
                            <td class="px-3 py-3 capitalize">{{ str_replace('_', ' ', $booking->booking_status) }}
                            </td>
                            <td class="px-3 py-3">RM {{ number_format((float) $booking->total_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-slate-500">You have no previous
                                bookings.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
