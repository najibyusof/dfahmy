@csrf

@php($task = $task ?? null)

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="room_id" class="block text-sm font-medium text-slate-700">Room</label>
        <select id="room_id" name="room_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            <option value="">Select room</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}" @selected((string) old('room_id', $task?->room_id ?? '') === (string) $room->id)>{{ $room->code }} -
                    {{ $room->name }}</option>
            @endforeach
        </select>
        @error('room_id')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="booking_id" class="block text-sm font-medium text-slate-700">Related Booking (optional)</label>
        <select id="booking_id" name="booking_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            <option value="">No booking</option>
            @foreach ($bookings as $booking)
                <option value="{{ $booking->id }}" @selected((string) old('booking_id', $task?->booking_id ?? '') === (string) $booking->id)>{{ $booking->booking_reference }}
                </option>
            @endforeach
        </select>
        @error('booking_id')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="assigned_to_user_id" class="block text-sm font-medium text-slate-700">Assigned User</label>
        <select id="assigned_to_user_id" name="assigned_to_user_id"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            <option value="">Select housekeeper</option>
            @foreach ($housekeepers as $housekeeper)
                <option value="{{ $housekeeper->id }}" @selected((string) old('assigned_to_user_id', $task?->assigned_to_user_id ?? '') === (string) $housekeeper->id)>{{ $housekeeper->name }}</option>
            @endforeach
        </select>
        @error('assigned_to_user_id')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="task_type" class="block text-sm font-medium text-slate-700">Task Type</label>
        <select id="task_type" name="task_type" class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            @foreach ($taskTypes as $taskType)
                <option value="{{ $taskType }}" @selected(old('task_type', $task?->task_type ?? 'other') === $taskType)>
                    {{ str_replace('_', ' ', $taskType) }}</option>
            @endforeach
        </select>
        @error('task_type')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="priority" class="block text-sm font-medium text-slate-700">Priority</label>
        <select id="priority" name="priority" class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            @foreach ($priorities as $priority)
                <option value="{{ $priority }}" @selected(old('priority', $task?->priority ?? 'medium') === $priority)>
                    {{ str_replace('_', ' ', $priority) }}</option>
            @endforeach
        </select>
        @error('priority')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="due_date" class="block text-sm font-medium text-slate-700">Due Date</label>
        <input id="due_date" name="due_date" type="date"
            value="{{ old('due_date', $task?->due_date?->format('Y-m-d') ?? now()->toDateString()) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('due_date')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
        <select id="status" name="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $task?->status ?? 'pending') === $status)>{{ str_replace('_', ' ', $status) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="completed_at" class="block text-sm font-medium text-slate-700">Completed At (optional)</label>
        <input id="completed_at" name="completed_at" type="datetime-local"
            value="{{ old('completed_at', $task?->completed_at?->format('Y-m-d\TH:i') ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm">
        @error('completed_at')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
        <textarea id="notes" name="notes" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('notes', $task?->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="checklist_notes" class="block text-sm font-medium text-slate-700">Checklist / Notes</label>
        <textarea id="checklist_notes" name="checklist_notes" rows="3"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('checklist_notes', $task?->checklist_notes ?? '') }}</textarea>
        @error('checklist_notes')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit"
        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save
        Task</button>
    <a href="{{ route('housekeeping.manage.index') }}"
        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancel</a>
</div>
