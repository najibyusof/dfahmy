<x-guest-layout>
    <header class="mb-6">
        <p class="text-xs uppercase tracking-[0.18em] text-[#6a826f]">Security Check</p>
        <h1 class="mt-2 font-serif text-3xl text-[#1f3a2f]">Confirm your password</h1>
    </header>

    <div class="mb-4 text-sm leading-7 text-slate-700">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="pt-2">
            <button type="submit"
                class="inline-flex w-full items-center justify-center rounded-md bg-[#1f3a2f] px-5 py-3 text-sm font-semibold text-[#f6f1e8] transition hover:bg-[#183027] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1f3a2f] focus-visible:ring-offset-2">
                {{ __('Confirm') }}
            </button>
        </div>
    </form>
</x-guest-layout>
