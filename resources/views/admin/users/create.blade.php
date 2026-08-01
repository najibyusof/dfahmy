<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            Add System User
        </h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="max-w-2xl">
            <h3 class="text-lg font-semibold text-slate-900">Create New User Account</h3>
            <p class="mt-1 text-sm text-slate-600">Only Super Admin can create system users and assign roles.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                @csrf

                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-slate-700">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}"
                        class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                        class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="role" class="block text-sm font-medium text-slate-700">Role</label>
                    <select id="role" name="role" class="mt-1 w-full rounded-lg border-slate-300 text-sm"
                        required>
                        <option value="">Select role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password"
                        class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm
                        Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                        class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
                </div>

                <div class="sm:col-span-2 mt-2 flex items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Create User
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </section>
</x-app-layout>
