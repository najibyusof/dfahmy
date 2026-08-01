<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Rooms</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('rooms.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search name/code"
                    class="rounded-lg border-slate-300 text-sm">
                <select name="building_id" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Buildings</option>
                    @foreach ($buildings as $building)
                        <option value="{{ $building->id }}" @selected($filters['building_id'] === (string) $building->id)>{{ $building->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>
                            {{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
                <select name="active" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Active States</option>
                    <option value="1" @selected($filters['active'] === '1')>Active</option>
                    <option value="0" @selected($filters['active'] === '0')>Inactive</option>
                </select>
                <select name="lifecycle" class="rounded-lg border-slate-300 text-sm">
                    <option value="active" @selected(($filters['lifecycle'] ?? 'active') === 'active')>Active Records</option>
                    <option value="archived" @selected(($filters['lifecycle'] ?? 'active') === 'archived')>Archived</option>
                    <option value="all" @selected(($filters['lifecycle'] ?? 'active') === 'all')>All Records</option>
                </select>
                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Filter</button>
                <a href="{{ route('rooms.index') }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Reset</a>
            </form>

            <div class="flex items-center gap-2">
                <a href="{{ route('rooms.availability-calendar') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Availability</a>
                <a href="{{ route('room-bed-configurations.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Room
                    Beds</a>
                <a href="{{ route('rooms.import.form') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Import
                    CSV</a>
                <a href="{{ route('rooms.export') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Export
                    CSV</a>
                <a href="{{ route('rooms.create') }}"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">New
                    Room</a>
            </div>
        </div>

        @php
            $lifecycle = $filters['lifecycle'] ?? 'active';
        @endphp

        <div class="mt-4 inline-flex rounded-lg border border-slate-300 p-1 text-xs font-semibold">
            <a href="{{ route('rooms.index', array_merge(request()->except('page'), ['lifecycle' => 'active'])) }}"
                class="rounded-md px-3 py-1 {{ $lifecycle === 'active' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Active
                <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $lifecycle === 'active' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $lifecycleCounts['active'] ?? 0 }}</span></a>
            <a href="{{ route('rooms.index', array_merge(request()->except('page'), ['lifecycle' => 'archived'])) }}"
                class="rounded-md px-3 py-1 {{ $lifecycle === 'archived' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Archived
                <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $lifecycle === 'archived' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $lifecycleCounts['archived'] ?? 0 }}</span></a>
            <a href="{{ route('rooms.index', array_merge(request()->except('page'), ['lifecycle' => 'all'])) }}"
                class="rounded-md px-3 py-1 {{ $lifecycle === 'all' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">All
                <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $lifecycle === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $lifecycleCounts['all'] ?? 0 }}</span></a>
        </div>
        <p class="mt-2 text-xs text-slate-500">Archived items are soft-deleted records and can be restored.</p>

        @if (session('status') === 'room-created')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Room created successfully.</p>
        @elseif (session('status') === 'room-updated')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Room updated successfully.</p>
        @elseif (session('status') === 'room-deleted')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Room deleted successfully.</p>
        @elseif (str_starts_with((string) session('status'), 'rooms-imported:'))
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
                Imported {{ (int) str_replace('rooms-imported:', '', (string) session('status')) }} room record(s)
                successfully.
            </p>
        @elseif (session('status') === 'room-restored')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Room restored successfully.</p>
        @endif

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Room</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Building</th>
                        <th class="px-4 py-3">Floor</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Rate</th>
                        <th class="px-4 py-3">Guests</th>
                        <th class="px-4 py-3">Active</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($rooms as $room)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $room->name }}</td>
                            <td class="px-4 py-3">{{ $room->code }}</td>
                            <td class="px-4 py-3">{{ $room->building->name }}</td>
                            <td class="px-4 py-3">{{ $room->floor }}</td>
                            <td class="px-4 py-3">{{ $room->room_type }}</td>
                            <td class="px-4 py-3">{{ str_replace('_', ' ', $room->status) }}</td>
                            <td class="px-4 py-3">RM {{ number_format((float) $room->base_nightly_rate, 2) }}</td>
                            <td class="px-4 py-3">{{ $room->maximum_guests }}</td>
                            <td class="px-4 py-3">{{ $room->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if ($room->trashed())
                                        <form method="POST" action="{{ route('rooms.restore', $room->id) }}">
                                            @csrf
                                            <button type="submit"
                                                class="rounded-lg border border-emerald-300 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Restore</button>
                                        </form>
                                    @else
                                        <a href="{{ route('rooms.edit', $room) }}"
                                            class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                                        <form method="POST" action="{{ route('rooms.destroy', $room) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-6 text-center text-slate-500">No rooms found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $rooms->links() }}</div>
    </section>
</x-app-layout>
