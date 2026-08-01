<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Room Availability Calendar</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('rooms.availability-calendar') }}"
                class="flex flex-wrap items-center gap-2">
                <input type="date" name="on_date" value="{{ $filters['on_date'] }}"
                    class="rounded-lg border-slate-300 text-sm">
                <select name="building_id" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Buildings</option>
                    @foreach ($buildings as $building)
                        <option value="{{ $building->id }}" @selected($filters['building_id'] === (string) $building->id)>{{ $building->name }}</option>
                    @endforeach
                </select>
                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Apply</button>
                <a href="{{ route('rooms.availability-calendar') }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Reset</a>
            </form>

            <a href="{{ route('rooms.index') }}"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back
                to Rooms</a>
        </div>

        <p class="mt-4 text-sm text-slate-600">Showing active room status for <span
                class="font-semibold text-slate-900">{{ $filters['on_date'] }}</span>.</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($rooms as $room)
                @php
                    $statusLabel = str_replace('_', ' ', $room->status);
                    $statusClass = $statusStyles[$room->status] ?? 'bg-slate-100 text-slate-800';
                @endphp
                <article class="rounded-xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-base font-semibold text-slate-900">{{ $room->name }}</p>
                            <p class="text-xs text-slate-500">{{ $room->code }} · {{ $room->building->name }} · Floor
                                {{ $room->floor }}</p>
                        </div>
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-2 text-xs text-slate-600">
                        <div>
                            <dt class="text-slate-500">Type</dt>
                            <dd class="font-medium text-slate-800">{{ $room->room_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Max Guests</dt>
                            <dd class="font-medium text-slate-800">{{ $room->maximum_guests }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-slate-500">Rate</dt>
                            <dd class="font-medium text-slate-800">RM
                                {{ number_format((float) $room->base_nightly_rate, 2) }}</dd>
                        </div>
                    </dl>
                </article>
            @empty
                <p class="text-sm text-slate-500">No active rooms found for this filter selection.</p>
            @endforelse
        </div>
    </section>
</x-app-layout>
