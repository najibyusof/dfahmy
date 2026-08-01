<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Housekeeping Management</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('housekeeping.manage.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ $filters['search'] }}"
                    placeholder="Search room, assignee, task" class="rounded-lg border-slate-300 text-sm">

                <select name="status" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>
                            {{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>

                <select name="priority" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Priorities</option>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority }}" @selected($filters['priority'] === $priority)>
                            {{ str_replace('_', ' ', $priority) }}</option>
                    @endforeach
                </select>

                <select name="assigned_to_user_id" class="rounded-lg border-slate-300 text-sm">
                    <option value="">All Assignees</option>
                    @foreach ($housekeepers as $housekeeper)
                        <option value="{{ $housekeeper->id }}" @selected($filters['assigned_to_user_id'] === (string) $housekeeper->id)>{{ $housekeeper->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Filter</button>
                <a href="{{ route('housekeeping.manage.index') }}"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Reset</a>
            </form>

            <a href="{{ route('housekeeping.manage.create') }}"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">New
                Task</a>
        </div>

        @if (session('status') === 'housekeeping-task-created')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Housekeeping task created
                successfully.</p>
        @elseif (session('status') === 'housekeeping-task-updated')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Housekeeping task updated
                successfully.</p>
        @elseif (session('status') === 'housekeeping-task-deleted')
            <p class="mt-4 rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-700">Housekeeping task deleted
                successfully.</p>
        @endif

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Room</th>
                        <th class="px-3 py-2">Assignee</th>
                        <th class="px-3 py-2">Task Type</th>
                        <th class="px-3 py-2">Priority</th>
                        <th class="px-3 py-2">Due Date</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Completed At</th>
                        <th class="px-3 py-2">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($tasks as $task)
                        @php
                            $statusBadgeClasses = [
                                'pending' => 'border border-amber-200 bg-amber-50 text-amber-700',
                                'in_progress' => 'border border-blue-200 bg-blue-50 text-blue-700',
                                'completed' => 'border border-emerald-200 bg-emerald-50 text-emerald-700',
                                'cancelled' => 'border border-slate-300 bg-slate-100 text-slate-700',
                            ];
                        @endphp
                        <tr>
                            <td class="px-3 py-2 font-medium text-slate-900">
                                {{ $task->room?->code ?? $task->room_label }}</td>
                            <td class="px-3 py-2">{{ $task->assignee?->name ?? '-' }}</td>
                            <td class="px-3 py-2">{{ str_replace('_', ' ', $task->task_type) }}</td>
                            <td class="px-3 py-2">{{ str_replace('_', ' ', $task->priority) }}</td>
                            <td class="px-3 py-2">{{ $task->due_date?->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-3 py-2"><span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses[$task->status] ?? 'border border-slate-200 bg-slate-50 text-slate-700' }}">{{ str_replace('_', ' ', $task->status) }}</span>
                            </td>
                            <td class="px-3 py-2">{{ $task->completed_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('housekeeping.manage.edit', $task) }}"
                                        class="rounded-lg border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                                    <form method="POST" action="{{ route('housekeeping.manage.destroy', $task) }}"
                                        onsubmit="return confirm('Delete this housekeeping task?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded-lg border border-rose-300 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-slate-500">No housekeeping tasks found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $tasks->links() }}</div>
    </section>
</x-app-layout>
