<x-guest-layout>
    <header class="mb-6">
        <p class="text-xs uppercase tracking-[0.18em] text-[#6a826f]">Create Account</p>
        <h1 class="mt-2 font-serif text-3xl text-[#1f3a2f]">Start your retreat journey</h1>
        <p class="mt-2 text-sm leading-7 text-slate-700">Create your guest account to browse available dates, request
            a room, and review your bookings.</p>
    </header>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')"
                required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')"
                required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone_number" :value="__('Phone Number')" />
            <x-text-input id="phone_number" class="mt-1 block w-full" type="tel" name="phone_number"
                :value="old('phone_number')" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="identification_number" :value="__('Identification Number')" />
            <x-text-input id="identification_number" class="mt-1 block w-full" type="text"
                name="identification_number" :value="old('identification_number')" required />
            <x-input-error :messages="$errors->get('identification_number')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <button type="submit"
                class="inline-flex w-full items-center justify-center rounded-md bg-[#1f3a2f] px-5 py-3 text-sm font-semibold text-[#f6f1e8] transition hover:bg-[#183027] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1f3a2f] focus-visible:ring-offset-2">
                {{ __('Register') }}
            </button>
        </div>

        <p class="pt-1 text-center text-sm text-slate-700">
            {{ __('Already registered?') }}
            <a href="{{ route('login') }}"
                class="font-semibold text-[#2f4f3f] underline decoration-[#b59a78] underline-offset-4 transition hover:text-[#1f3a2f]">
                Log in
            </a>
        </p>
    </form>
</x-guest-layout>
