@csrf

@php($guest = $guest ?? null)

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="full_name" class="block text-sm font-medium text-slate-700">Full Name</label>
        <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $guest->full_name ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('full_name')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $guest->email ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('email')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone_number" class="block text-sm font-medium text-slate-700">Phone Number</label>
        <input id="phone_number" name="phone_number" type="text"
            value="{{ old('phone_number', $guest->phone_number ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('phone_number')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="identification_number" class="block text-sm font-medium text-slate-700">Identification or Passport
            Number</label>
        <input id="identification_number" name="identification_number" type="text"
            value="{{ old('identification_number', $guest->identification_number ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
        @error('identification_number')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="address" class="block text-sm font-medium text-slate-700">Address</label>
        <textarea id="address" name="address" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('address', $guest->address ?? '') }}</textarea>
        @error('address')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="nationality" class="block text-sm font-medium text-slate-700">Nationality</label>
        <input id="nationality" name="nationality" type="text"
            value="{{ old('nationality', $guest->nationality ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm">
        @error('nationality')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div></div>

    <div>
        <label for="emergency_contact_name" class="block text-sm font-medium text-slate-700">Emergency Contact
            Name</label>
        <input id="emergency_contact_name" name="emergency_contact_name" type="text"
            value="{{ old('emergency_contact_name', $guest->emergency_contact_name ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm">
        @error('emergency_contact_name')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="emergency_contact_phone" class="block text-sm font-medium text-slate-700">Emergency Contact
            Phone</label>
        <input id="emergency_contact_phone" name="emergency_contact_phone" type="text"
            value="{{ old('emergency_contact_phone', $guest->emergency_contact_phone ?? '') }}"
            class="mt-1 w-full rounded-lg border-slate-300 text-sm">
        @error('emergency_contact_phone')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
        <textarea id="notes" name="notes" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm">{{ old('notes', $guest->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit"
        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save</button>
    <a href="{{ route('guests.index') }}"
        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancel</a>
</div>
