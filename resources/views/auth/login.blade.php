<x-guest-layout>
    <header class="mb-6">
        <p class="text-xs uppercase tracking-[0.18em] text-[#6a826f]">Guest Access</p>
        <h1 class="mt-2 font-serif text-3xl text-[#1f3a2f]">Welcome back</h1>
        <p class="mt-2 text-sm leading-7 text-slate-700">Log in to manage your stay details and bookings at DFahMy Eco
            Resort.</p>
    </header>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')"
                required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-[#c8bba3] text-[#1f3a2f] shadow-sm focus:ring-[#1f3a2f]" name="remember">
                <span class="ms-2 text-sm text-slate-700">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-[#2f4f3f] underline decoration-[#b59a78] underline-offset-4 transition hover:text-[#1f3a2f] focus:outline-none focus:ring-2 focus:ring-[#1f3a2f] focus:ring-offset-2"
                    href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit"
                class="inline-flex w-full items-center justify-center rounded-md bg-[#1f3a2f] px-5 py-3 text-sm font-semibold text-[#f6f1e8] transition hover:bg-[#183027] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1f3a2f] focus-visible:ring-offset-2">
                {{ __('Log in') }}
            </button>
        </div>

        @if (Route::has('register'))
            <p class="pt-1 text-center text-sm text-slate-700">
                New to DFahMy?
                <a href="{{ route('register') }}"
                    class="font-semibold text-[#2f4f3f] underline decoration-[#b59a78] underline-offset-4 transition hover:text-[#1f3a2f]">
                    Create an account
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>
