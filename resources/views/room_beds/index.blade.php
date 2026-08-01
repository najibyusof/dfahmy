<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Room Bed Configuration</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('room-bed-configurations.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search room name/code"
                    class="rounded-lg border-slate-300 text-sm">
                <select name="building_id" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Buildings</option>
                    @foreach ($buildings as $building)
                        <option value="{{ $building->id }}" @selected($filters['building_id'] === (string) $building->id)>{{ $building->name }}</option>
                    @endforeach
                </select>
                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Filter</button>
                <a href="{{ route('room-bed-configurations.index') }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Reset</a>
            </form>

            <a href="{{ route('rooms.index') }}"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back
                to Rooms</a>
        </div>

        @if (session('status') === 'room-beds-updated')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Room bed configuration updated.
            </p>
        @endif

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Room</th>
                        <th class="px-4 py-3">Building</th>
                        <th class="px-4 py-3">Queen Beds</th>
                        <th class="px-4 py-3">Sofa Beds</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($rooms as $room)
                        @php
                            $queen = (int) $room->beds->firstWhere('bed_type', 'queen_bed')?->quantity;
                            $sofa = (int) $room->beds->firstWhere('bed_type', 'sofa_bed')?->quantity;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $room->name }} ({{ $room->code }})
                            </td>
                            <td class="px-4 py-3">{{ $room->building->name }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('room-bed-configurations.update', $room) }}"
                                    class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                    <input type="hidden" name="building_id" value="{{ $filters['building_id'] }}">
                                    <input type="number" name="queen_bed_quantity" min="0" max="20"
                                        value="{{ old('queen_bed_quantity', $queen) }}"
                                        class="w-20 rounded-lg border-slate-300 text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="sofa_bed_quantity" min="0" max="20"
                                    value="{{ old('sofa_bed_quantity', $sofa) }}"
                                    class="w-20 rounded-lg border-slate-300 text-sm">
                            </td>
                            <td class="px-4 py-3">
                                <button type="submit"
                                    class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Save</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No rooms found for bed
                                configuration.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $rooms->links() }}</div>
    </section>
</x-app-layout>
