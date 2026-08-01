<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            User Management
        </h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">System Users</h3>
                <p class="mt-1 text-sm text-slate-600">Assign roles to control access to resort modules.</p>
            </div>
        </div>

        @if (session('status') === 'user-role-updated')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">User role updated successfully.
            </p>
        @endif

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Current Role</th>
                        <th class="px-4 py-3">Update Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @foreach ($users as $managedUser)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $managedUser->name }}</td>
                            <td class="px-4 py-3">{{ $managedUser->email }}</td>
                            <td class="px-4 py-3">{{ $managedUser->getRoleNames()->first() ?? 'No Role' }}</td>
                            <td class="px-4 py-3">
                                @can('updateRole', $managedUser)
                                    <form method="POST" action="{{ route('admin.users.role.update', $managedUser) }}"
                                        class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="rounded-lg border-slate-300 text-sm">
                                            @foreach ($roles as $role)
                                                <option value="{{ $role }}" @selected($managedUser->hasRole($role))>
                                                    {{ $role }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit"
                                            class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                            Save
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500">Not allowed</span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Recent Role Changes</h3>
                <p class="mt-1 text-sm text-slate-600">Filter and export role assignment history for compliance
                    tracking.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.users.index',['from_date' => now()->toDateString(), 'to_date' => now()->toDateString()] +collect(request()->query())->except(['from_date', 'to_date', 'page'])->toArray()) }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                    Today
                </a>
                <a href="{{ route('admin.users.index',['from_date' => now()->subDays(6)->toDateString(), 'to_date' => now()->toDateString()] +collect(request()->query())->except(['from_date', 'to_date', 'page'])->toArray()) }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                    Last 7 Days
                </a>
                <a href="{{ route('admin.users.index',['from_date' => now()->subDays(29)->toDateString(), 'to_date' => now()->toDateString()] +collect(request()->query())->except(['from_date', 'to_date', 'page'])->toArray()) }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                    Last 30 Days
                </a>
                <a href="{{ route('admin.users.audit.export', request()->query()) }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                    Export CSV
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        @php
            $activeBadges = [
                !empty($filters['actor']) ? 'Actor: ' . $filters['actor'] : null,
                !empty($filters['target']) ? 'Target: ' . $filters['target'] : null,
                !empty($filters['from_role']) ? 'From: ' . $filters['from_role'] : null,
                !empty($filters['to_role']) ? 'To: ' . $filters['to_role'] : null,
                !empty($filters['from_date']) ? 'Start: ' . $filters['from_date'] : null,
                !empty($filters['to_date']) ? 'End: ' . $filters['to_date'] : null,
                !empty($filters['sort']) ? 'Sort: ' . ($sortOptions[$filters['sort']] ?? $filters['sort']) : null,
                !empty($filters['per_page']) ? 'Page Size: ' . $filters['per_page'] : null,
            ];
            $activeBadges = array_values(array_filter($activeBadges));
        @endphp

        @if (!empty($activeBadges))
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($activeBadges as $badge)
                    <span
                        class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                        {{ $badge }}
                    </span>
                @endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('admin.users.index') }}"
            class="mt-5 grid gap-3 rounded-xl border border-slate-200 p-4 md:grid-cols-8">
            <input type="text" name="actor" value="{{ $filters['actor'] ?? '' }}"
                placeholder="Actor name or email" class="rounded-lg border-slate-300 text-sm">
            <input type="text" name="target" value="{{ $filters['target'] ?? '' }}"
                placeholder="Target name or email" class="rounded-lg border-slate-300 text-sm">
            <select name="from_role" class="rounded-lg border-slate-300 text-sm">
                <option value="">From role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(($filters['from_role'] ?? '') === $role)>{{ $role }}</option>
                @endforeach
            </select>
            <select name="to_role" class="rounded-lg border-slate-300 text-sm">
                <option value="">To role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(($filters['to_role'] ?? '') === $role)>{{ $role }}</option>
                @endforeach
            </select>
            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}"
                class="rounded-lg border-slate-300 text-sm">
            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}"
                class="rounded-lg border-slate-300 text-sm">
            <select name="sort" class="rounded-lg border-slate-300 text-sm">
                @foreach ($sortOptions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['sort'] ?? 'created_at_desc') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <select name="per_page" class="rounded-lg border-slate-300 text-sm">
                @foreach ($perPageOptions as $size)
                    <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 10) === $size)>
                        {{ $size }} / page
                    </option>
                @endforeach
            </select>
            <div class="flex items-center gap-2">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                    Apply
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                    Reset
                </a>
            </div>
        </form>

        @if ($auditLogs->isEmpty())
            <p class="mt-4 text-sm text-slate-600">No role changes recorded yet.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">When</th>
                            <th class="px-4 py-3">Changed By</th>
                            <th class="px-4 py-3">Target User</th>
                            <th class="px-4 py-3">From</th>
                            <th class="px-4 py-3">To</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @foreach ($auditLogs as $log)
                            <tr>
                                <td class="px-4 py-3">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3">{{ $log->actor?->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $log->target?->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $log->from_role ?? 'No Role' }}</td>
                                <td class="px-4 py-3">{{ $log->to_role }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </section>
</x-app-layout>
