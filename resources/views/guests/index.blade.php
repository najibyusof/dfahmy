<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Guests</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('guests.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ $filters['search'] }}"
                    placeholder="Search name, phone, email, ID" class="rounded-lg border-slate-300 text-sm">
                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Search</button>
                <a href="{{ route('guests.index') }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Reset</a>
            </form>

            <a href="{{ route('guests.create') }}"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">New
                Guest</a>
        </div>

        @if (session('status') === 'guest-created')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Guest created successfully.</p>
        @elseif (session('status') === 'guest-updated')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Guest updated successfully.</p>
        @elseif (session('status') === 'guest-deleted')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Guest deleted successfully.</p>
        @elseif (session('status') === 'guest-delete-blocked')
            <p class="mt-4 rounded-lg bg-amber-50 px-4 py-2 text-sm text-amber-700">Guest cannot be deleted because
                related bookings exist.</p>
        @endif

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">ID/Passport</th>
                        <th class="px-4 py-3">Nationality</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($guests as $guest)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $guest->full_name }}</td>
                            <td class="px-4 py-3">{{ $guest->email }}</td>
                            <td class="px-4 py-3">{{ $guest->phone_number }}</td>
                            <td class="px-4 py-3">{{ $guest->identification_number }}</td>
                            <td class="px-4 py-3">{{ $guest->nationality ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('guests.show', $guest) }}"
                                        class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Profile</a>
                                    <a href="{{ route('guests.edit', $guest) }}"
                                        class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                                    <form method="POST" action="{{ route('guests.destroy', $guest) }}">
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
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">No guests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $guests->links() }}</div>
    </section>
</x-app-layout>
