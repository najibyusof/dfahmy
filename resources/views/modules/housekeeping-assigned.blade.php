<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            My Housekeeping Tasks
        </h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">Assigned Tasks</h3>

        @if (session('status') === 'housekeeping-task-updated')
            <p class="mt-3 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Task updated successfully.</p>
        @endif

        @if ($tasks->isEmpty())
            <p class="mt-4 text-sm text-slate-600">No housekeeping tasks are currently assigned to you.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Room</th>
                            <th class="px-4 py-3">Task</th>
                            <th class="px-4 py-3">Priority</th>
                            <th class="px-4 py-3">Due</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Notes</th>
                            <th class="px-4 py-3">Checklist / Notes</th>
                            <th class="px-4 py-3">Completed At</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @foreach ($tasks as $task)
                            @php
                                $statusBadgeClasses = [
                                    'pending' => 'border border-amber-200 bg-amber-50 text-amber-700',
                                    'in_progress' => 'border border-blue-200 bg-blue-50 text-blue-700',
                                    'completed' => 'border border-emerald-200 bg-emerald-50 text-emerald-700',
                                    'cancelled' => 'border border-slate-300 bg-slate-100 text-slate-700',
                                ];
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ $task->room?->code ?? $task->room_label }}</td>
                                <td class="px-4 py-3">{{ str_replace('_', ' ', $task->task_type) }}</td>
                                <td class="px-4 py-3">{{ str_replace('_', ' ', $task->priority) }}</td>
                                <td class="px-4 py-3">{{ $task->due_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadgeClasses[$task->status] ?? 'border border-slate-200 bg-slate-50 text-slate-700' }}">{{ str_replace('_', ' ', $task->status) }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $task->notes ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $task->checklist_notes ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $task->completed_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @can('update', $task)
                                        <form method="POST" action="{{ route('housekeeping.tasks.update', $task) }}"
                                            class="space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="w-full rounded-lg border-slate-300 text-sm">
                                                <option value="pending" @selected($task->status === 'pending')>Pending</option>
                                                <option value="in_progress" @selected($task->status === 'in_progress')>In Progress
                                                </option>
                                                <option value="completed" @selected($task->status === 'completed')>Completed</option>
                                            </select>
                                            <textarea name="checklist_notes" rows="2" class="w-full rounded-lg border-slate-300 text-sm"
                                                placeholder="Checklist updates">{{ $task->checklist_notes }}</textarea>
                                            <button type="submit"
                                                class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                                Update
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-500">No permission</span>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-app-layout>
