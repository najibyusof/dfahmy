@csrf

@php
    $bookableUnit = $bookableUnit ?? null;
    $selectedRooms = collect(old('room_ids', $bookableUnit?->rooms?->pluck('id')->all() ?? []))
        ->map(fn($id) => (string) $id)
        ->all();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $bookableUnit?->name ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('name')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Code</label>
        <input id="code" name="code" type="text" value="{{ old('code', $bookableUnit?->code ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('code')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="booking_type" class="block text-sm font-medium text-slate-700">Booking Type</label>
        <select id="booking_type" name="booking_type" class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('booking_type', $bookableUnit?->booking_type ?? 'room') === $type)>{{ str_replace('_', ' ', $type) }}
                </option>
            @endforeach
        </select>
        @error('booking_type')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="base_nightly_rate" class="block text-sm font-medium text-slate-700">Base Nightly Rate</label>
        <input id="base_nightly_rate" name="base_nightly_rate" type="number" min="0" step="0.01"
            value="{{ old('base_nightly_rate', $bookableUnit?->base_nightly_rate ?? 0) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('base_nightly_rate')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="maximum_guests" class="block text-sm font-medium text-slate-700">Maximum Guests</label>
        <input id="maximum_guests" name="maximum_guests" type="number" min="1"
            value="{{ old('maximum_guests', $bookableUnit?->maximum_guests ?? 1) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('maximum_guests')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-slate-700">Sort Order</label>
        <input id="sort_order" name="sort_order" type="number" min="0"
            value="{{ old('sort_order', $bookableUnit?->sort_order ?? 0) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('sort_order')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('description', $bookableUnit?->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-emerald-600"
                @checked((bool) old('is_active', $bookableUnit?->is_active ?? true))>
            Active
        </label>
    </div>
</div>

<section class="mt-6 rounded-xl border border-slate-200 p-4">
    <h3 class="text-sm font-semibold text-slate-900">Assign Rooms</h3>
    <p class="mt-1 text-xs text-slate-500">A room can belong to multiple bookable units.</p>

    <div class="mt-4 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($rooms as $room)
            <label class="inline-flex items-start gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <input type="checkbox" name="room_ids[]" value="{{ $room->id }}"
                    class="mt-1 rounded border-slate-300 text-emerald-600" @checked(in_array((string) $room->id, $selectedRooms, true))>
                <span>
                    <span class="font-medium text-slate-800">{{ $room->code }}</span>
                    <span class="text-slate-600">- {{ $room->name }}</span>
                    <span class="block text-xs text-slate-500">{{ $room->building?->name }} | Floor
                        {{ $room->floor }}</span>
                </span>
            </label>
        @endforeach
    </div>

    @error('room_ids')
        <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
    @enderror
    @error('room_ids.*')
        <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
    @enderror
</section>

<div class="mt-6 flex items-center gap-3">
    <button type="submit"
        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save</button>
    <a href="{{ route('bookable-units.index') }}"
        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancel</a>
</div>
