<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Edit Booking</h2>
    </x-slot>

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Room Availability Search</h3>
        <form method="GET" action="{{ route('bookings.edit', $booking) }}" class="mt-3 grid gap-3 md:grid-cols-3">
            <div>
                <label for="availability_check_in" class="block text-xs font-medium text-slate-600">Check In</label>
                <input id="availability_check_in" name="availability_check_in" type="date"
                    value="{{ $availabilityFilters['check_in_date'] }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label for="availability_check_out" class="block text-xs font-medium text-slate-600">Check Out</label>
                <input id="availability_check_out" name="availability_check_out" type="date"
                    value="{{ $availabilityFilters['check_out_date'] }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label for="availability_building_id" class="block text-xs font-medium text-slate-600">Building</label>
                <select id="availability_building_id" name="availability_building_id"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                    <option value="">All Buildings</option>
                    @foreach ($buildings as $building)
                        <option value="{{ $building->id }}" @selected($availabilityFilters['building_id'] === (string) $building->id)>{{ $building->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="availability_room_id" class="block text-xs font-medium text-slate-600">Room</label>
                <select id="availability_room_id" name="availability_room_id"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                    <option value="">All Rooms</option>
                    @foreach ($allRooms as $room)
                        <option value="{{ $room->id }}" @selected($availabilityFilters['room_id'] === (string) $room->id)>{{ $room->code }} -
                            {{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="availability_adults" class="block text-xs font-medium text-slate-600">Adults</label>
                <input id="availability_adults" name="availability_adults" type="number" min="1"
                    value="{{ $availabilityFilters['adults'] }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label for="availability_children" class="block text-xs font-medium text-slate-600">Children</label>
                <input id="availability_children" name="availability_children" type="number" min="0"
                    value="{{ $availabilityFilters['children'] }}"
                    class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div class="md:col-span-3">
                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Search
                    Availability</button>
            </div>
        </form>
        @if ($availabilitySearched)
            <p class="mt-3 text-xs text-slate-600">Available rooms found: {{ $rooms->count() }}</p>
        @endif
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('bookings.update', $booking) }}">
            @method('PATCH')
            @include('bookings._form')
        </form>
    </section>
</x-app-layout>
