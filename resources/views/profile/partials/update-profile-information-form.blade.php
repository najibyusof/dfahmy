<section>
    @php
        $hasTelegramChatId = is_string($user->telegram_chat_id) && trim($user->telegram_chat_id) !== '';
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information, email address, and Telegram chat ID.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        @if (session('status') === 'telegram-chat-id-required')
            <p class="rounded-lg bg-amber-50 px-4 py-2 text-sm text-amber-800">Add your Telegram chat ID before sending a
                test message.</p>
        @elseif (session('status') === 'profile-telegram-test-queued')
            <p class="rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Telegram test message queued for your
                chat ID.</p>
        @endif

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="telegram_chat_id" :value="__('Telegram chat ID')" />
            <x-text-input id="telegram_chat_id" name="telegram_chat_id" type="text" class="mt-1 block w-full"
                :value="old('telegram_chat_id', $user->telegram_chat_id)" autocomplete="off" placeholder="-1001234567890" />
            <p class="mt-2 text-sm text-gray-600">Telegram alerts are sent to internal users with roles. This chat ID
                can only be updated from your profile page.</p>
            <x-input-error class="mt-2" :messages="$errors->get('telegram_chat_id')" />
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
            <h3 class="font-semibold text-amber-900">How to get your Telegram chat ID</h3>
            <ol class="mt-3 list-decimal space-y-2 pl-5">
                <li>Open Telegram and start a private chat with the resort bot, or open the group where you should
                    receive alerts.</li>
                <li>Send a message such as <span class="font-semibold">/start</span> so Telegram records the chat.</li>
                <li>Open <span
                        class="font-semibold">https://api.telegram.org/bot&lt;YOUR_BOT_TOKEN&gt;/getUpdates</span> in
                    your browser.</li>
                <li>Copy the <span class="font-semibold">chat.id</span> value from your private chat or target group,
                    then paste it in the field above.</li>
                <li>Do not use the bot's own ID. Telegram will reject messages sent to the bot itself.</li>
            </ol>
        </div>

        <div class="flex items-center gap-3">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>

    <form method="post" action="{{ route('profile.telegram-test') }}" class="mt-4">
        @csrf
        @if ($user->telegram_test_queued_at)
            <p class="mb-3 text-sm text-slate-600">Last self-test queued:
                {{ $user->telegram_test_queued_at->format('Y-m-d H:i:s') }}</p>
        @endif
        <button type="submit" @disabled(!$hasTelegramChatId)
            class="rounded-lg border px-4 py-2 text-sm font-semibold transition {{ $hasTelegramChatId ? 'border-slate-300 text-slate-700 hover:bg-slate-100' : 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' }}">
            Send Test Telegram To Me
        </button>
        @unless ($hasTelegramChatId)
            <p class="mt-2 text-sm text-slate-500">Save your Telegram chat ID first to enable the self-test button.</p>
        @endunless
    </form>

    <div class="flex items-center gap-4">
        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600">{{ __('Saved.') }}</p>
        @endif
    </div>
</section>
