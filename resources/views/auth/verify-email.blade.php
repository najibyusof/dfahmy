<x-guest-layout>
    <header class="mb-6">
        <p class="text-xs uppercase tracking-[0.18em] text-[#6a826f]">Verify Email</p>
        <h1 class="mt-2 font-serif text-3xl text-[#1f3a2f]">Confirm your email address</h1>
    </header>

    <div class="mb-4 text-sm leading-7 text-slate-700">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div
            class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-md bg-[#1f3a2f] px-5 py-3 text-sm font-semibold text-[#f6f1e8] transition hover:bg-[#183027] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1f3a2f] focus-visible:ring-offset-2 sm:w-auto">
                    {{ __('Resend Verification Email') }}
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit"
                class="text-sm font-medium text-[#2f4f3f] underline decoration-[#b59a78] underline-offset-4 transition hover:text-[#1f3a2f] focus:outline-none focus:ring-2 focus:ring-[#1f3a2f] focus:ring-offset-2">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
