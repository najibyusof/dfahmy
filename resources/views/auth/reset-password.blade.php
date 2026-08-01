<x-guest-layout>
    <header class="mb-6">
        <p class="text-xs uppercase tracking-[0.18em] text-[#6a826f]">Secure Reset</p>
        <h1 class="mt-2 font-serif text-3xl text-[#1f3a2f]">Choose a new password</h1>
    </header>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email', $request->email)"
                required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
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
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>
</x-guest-layout>
