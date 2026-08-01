@csrf

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $building->name ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('name')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Code</label>
        <input id="code" name="code" type="text" value="{{ old('code', $building->code ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('code')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="number_of_floors" class="block text-sm font-medium text-slate-700">Number of Floors</label>
        <input id="number_of_floors" name="number_of_floors" type="number" min="1"
            value="{{ old('number_of_floors', $building->number_of_floors ?? 1) }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('number_of_floors')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-2 pt-6">
        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-slate-300"
            @checked(old('is_active', $building->is_active ?? true))>
        <label for="is_active" class="text-sm font-medium text-slate-700">Active</label>
        @error('is_active')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('description', $building->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit"
        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save</button>
    <a href="{{ route('buildings.index') }}"
        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancel</a>
</div>
