@csrf

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="building_id" class="block text-sm font-medium text-slate-700">Building</label>
        <select id="building_id" name="building_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            <option value="">Select building</option>
            @foreach ($buildings as $building)
                <option value="{{ $building->id }}" @selected((string) old('building_id', $room->building_id ?? '') === (string) $building->id)>{{ $building->name }}</option>
            @endforeach
        </select>
        @error('building_id')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Room Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $room->name ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('name')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Room Code</label>
        <input id="code" name="code" type="text" value="{{ old('code', $room->code ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('code')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="floor" class="block text-sm font-medium text-slate-700">Floor</label>
        <input id="floor" name="floor" type="number" min="1" value="{{ old('floor', $room->floor ?? 1) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('floor')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="room_type" class="block text-sm font-medium text-slate-700">Room Type</label>
        <input id="room_type" name="room_type" type="text" value="{{ old('room_type', $room->room_type ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('room_type')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
        <select id="status" name="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $room->status ?? 'available') === $status)>{{ str_replace('_', ' ', $status) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="base_nightly_rate" class="block text-sm font-medium text-slate-700">Base Nightly Rate</label>
        <input id="base_nightly_rate" name="base_nightly_rate" type="number" step="0.01" min="0"
            value="{{ old('base_nightly_rate', $room->base_nightly_rate ?? 0) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('base_nightly_rate')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="maximum_guests" class="block text-sm font-medium text-slate-700">Maximum Guests</label>
        <input id="maximum_guests" name="maximum_guests" type="number" min="1"
            value="{{ old('maximum_guests', $room->maximum_guests ?? 1) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('maximum_guests')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
        <textarea id="notes" name="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('notes', $room->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-2 md:col-span-2">
        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-slate-300"
            @checked(old('is_active', $room->is_active ?? true))>
        <label for="is_active" class="text-sm font-medium text-slate-700">Active</label>
        @error('is_active')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit"
        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save</button>
    <a href="{{ route('rooms.index') }}"
        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancel</a>
</div>
