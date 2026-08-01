<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Bookable Units</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-slate-600">Manage room, group, floor, building, and whole-resort sellable inventory.
            </p>
            <a href="{{ route('bookable-units.create') }}"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">New
                Bookable Unit</a>
        </div>

        @if (session('status') === 'bookable-unit-created')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Bookable unit created
                successfully.</p>
        @elseif (session('status') === 'bookable-unit-updated')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Bookable unit updated
                successfully.</p>
        @elseif (session('status') === 'bookable-unit-deleted')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Bookable unit deleted
                successfully.</p>
        @endif

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Code</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2">Rate</th>
                        <th class="px-3 py-2">Max Guests</th>
                        <th class="px-3 py-2">Rooms</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($bookableUnits as $unit)
                        <tr>
                            <td class="px-3 py-2 font-medium text-slate-900">{{ $unit->name }}</td>
                            <td class="px-3 py-2">{{ $unit->code }}</td>
                            <td class="px-3 py-2">{{ str_replace('_', ' ', $unit->booking_type) }}</td>
                            <td class="px-3 py-2">RM {{ number_format((float) $unit->base_nightly_rate, 2) }}</td>
                            <td class="px-3 py-2">{{ $unit->maximum_guests }}</td>
                            <td class="px-3 py-2">{{ $unit->rooms->count() }}</td>
                            <td class="px-3 py-2">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-semibold {{ $unit->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">{{ $unit->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('bookable-units.edit', $unit) }}"
                                        class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                                    <form method="POST" action="{{ route('bookable-units.destroy', $unit) }}"
                                        onsubmit="return confirm('Delete this bookable unit?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded-lg border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-6 text-center text-slate-500">No bookable units found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $bookableUnits->links() }}</div>
    </section>
</x-app-layout>
