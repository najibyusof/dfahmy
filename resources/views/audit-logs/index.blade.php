<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Audit Logs</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid gap-3 md:grid-cols-5">
            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search subject or IP"
                class="rounded-lg border-slate-300 text-sm">
            <select name="action" class="rounded-lg border-slate-300 text-sm">
                <option value="">All Actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected($filters['action'] === $action)>
                        {{ str_replace('_', ' ', $action) }}</option>
                @endforeach
            </select>
            <select name="subject_type" class="rounded-lg border-slate-300 text-sm">
                <option value="">All Subjects</option>
                @foreach ($subjectTypes as $subjectType)
                    <option value="{{ $subjectType }}" @selected($filters['subject_type'] === $subjectType)>{{ class_basename($subjectType) }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ $filters['from'] }}"
                class="rounded-lg border-slate-300 text-sm">
            <input type="date" name="to" value="{{ $filters['to'] }}"
                class="rounded-lg border-slate-300 text-sm">
            <div class="md:col-span-5 flex gap-2"><button type="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Filter</button><a
                    href="{{ route('audit-logs.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Reset</a>
            </div>
        </form>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Time</th>
                        <th class="px-3 py-2">User</th>
                        <th class="px-3 py-2">Action</th>
                        <th class="px-3 py-2">Subject</th>
                        <th class="px-3 py-2">IP</th>
                        <th class="px-3 py-2">Changes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-3 py-2">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="px-3 py-2">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-3 py-2">{{ str_replace('_', ' ', $log->action) }}</td>
                            <td class="px-3 py-2">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                            </td>
                            <td class="px-3 py-2">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-3 py-2">
                                <details>
                                    <summary class="cursor-pointer text-emerald-700">View</summary>
                                    <div class="mt-2 text-xs">
                                        <p class="font-semibold text-slate-800">Old</p>
                                        <pre class="whitespace-pre-wrap rounded bg-slate-100 p-2">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        <p class="mt-2 font-semibold text-slate-800">New</p>
                                        <pre class="whitespace-pre-wrap rounded bg-slate-100 p-2">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-slate-500">No audit records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>
    </section>
</x-app-layout>
