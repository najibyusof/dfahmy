<x-guest-layout>
    <header class="mb-6">
        <p class="text-xs uppercase tracking-[0.18em] text-[#6a826f]">Password Recovery</p>
        <h1 class="mt-2 font-serif text-3xl text-[#1f3a2f]">Reset your password</h1>
    </header>

    <div class="mb-4 text-sm leading-7 text-slate-700">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')"
                required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="pt-2">
            <button type="submit"
                class="inline-flex w-full items-center justify-center rounded-md bg-[#1f3a2f] px-5 py-3 text-sm font-semibold text-[#f6f1e8] transition hover:bg-[#183027] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1f3a2f] focus-visible:ring-offset-2">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>
    </form>
</x-guest-layout>
