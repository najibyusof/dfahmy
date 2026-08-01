<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Buildings</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('buildings.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search name/code"
                    class="rounded-lg border-slate-300 text-sm">
                <select name="active" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Status</option>
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
                <a href="{{ route('buildings.index') }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Reset</a>
            </form>

            <a href="{{ route('buildings.create') }}"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">New
                Building</a>
        </div>

        @php
            $lifecycle = $filters['lifecycle'] ?? 'active';
        @endphp

        <div class="mt-4 inline-flex rounded-lg border border-slate-300 p-1 text-xs font-semibold">
            <a href="{{ route('buildings.index', array_merge(request()->except('page'), ['lifecycle' => 'active'])) }}"
                class="rounded-md px-3 py-1 {{ $lifecycle === 'active' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Active
                <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $lifecycle === 'active' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $lifecycleCounts['active'] ?? 0 }}</span></a>
            <a href="{{ route('buildings.index', array_merge(request()->except('page'), ['lifecycle' => 'archived'])) }}"
                class="rounded-md px-3 py-1 {{ $lifecycle === 'archived' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Archived
                <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $lifecycle === 'archived' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $lifecycleCounts['archived'] ?? 0 }}</span></a>
            <a href="{{ route('buildings.index', array_merge(request()->except('page'), ['lifecycle' => 'all'])) }}"
                class="rounded-md px-3 py-1 {{ $lifecycle === 'all' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">All
                <span
                    class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] {{ $lifecycle === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $lifecycleCounts['all'] ?? 0 }}</span></a>
        </div>
        <p class="mt-2 text-xs text-slate-500">Archived items are soft-deleted records and can be restored.</p>

        @if (session('status') === 'building-created')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Building created successfully.
            </p>
        @elseif (session('status') === 'building-updated')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Building updated successfully.
            </p>
        @elseif (session('status') === 'building-deleted')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Building deleted successfully.
            </p>
        @elseif (session('status') === 'building-delete-blocked')
            <p class="mt-4 rounded-lg bg-amber-50 px-4 py-2 text-sm text-amber-700">Building cannot be deleted because
                it still has rooms.</p>
        @elseif (session('status') === 'building-restored')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Building restored successfully.
            </p>
        @endif

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Floors</th>
                        <th class="px-4 py-3">Rooms</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($buildings as $building)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $building->name }}</td>
                            <td class="px-4 py-3">{{ $building->code }}</td>
                            <td class="px-4 py-3">{{ $building->number_of_floors }}</td>
                            <td class="px-4 py-3">{{ $building->rooms_count }}</td>
                            <td class="px-4 py-3">{{ $building->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if ($building->trashed())
                                        <form method="POST" action="{{ route('buildings.restore', $building->id) }}">
                                            @csrf
                                            <button type="submit"
                                                class="rounded-lg border border-emerald-300 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">Restore</button>
                                        </form>
                                    @else
                                        <a href="{{ route('buildings.edit', $building) }}"
                                            class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                                        <form method="POST" action="{{ route('buildings.destroy', $building) }}">
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
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">No buildings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $buildings->links() }}</div>
    </section>
</x-app-layout>
