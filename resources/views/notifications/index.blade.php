<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Notification Centre</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600">Track operational updates across bookings, payments, housekeeping,
                maintenance, and room status changes.</p>
            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Mark
                    All As Read</button>
            </form>
        </div>

        @if (session('status') === 'notification-marked-read')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Notification marked as read.</p>
        @elseif (session('status') === 'notifications-marked-read')
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">All notifications marked as
                read.</p>
        @endif

        @if ($notifications->isEmpty())
            <p class="mt-6 text-sm text-slate-600">No notifications yet.</p>
        @else
            <div class="mt-4 space-y-3">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $isRead = $notification->read_at !== null;
                        $title = $data['title'] ?? 'Notification';
                        $message = $data['message'] ?? '';
                        $type = $data['type'] ?? 'info';
                        $link = $data['link'] ?? route('dashboard');
                        $createdTime = $data['created_time'] ?? $notification->created_at?->toIso8601String();
                    @endphp
                    <article
                        class="rounded-xl border p-4 {{ $isRead ? 'border-slate-200 bg-white' : 'border-emerald-200 bg-emerald-50/40' }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $title }}</p>
                                <p class="mt-1 text-sm text-slate-700">{{ $message }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5">Type:
                                        {{ $type }}</span>
                                    <span>Created:
                                        {{ \Illuminate\Support\Carbon::parse($createdTime)->format('Y-m-d H:i') }}</span>
                                    <span>Status: {{ $isRead ? 'read' : 'unread' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ $link }}"
                                    class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Open</a>
                                @if (!$isRead)
                                    <form method="POST"
                                        action="{{ route('notifications.mark-read', $notification->id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Mark
                                            Read</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-4">{{ $notifications->links() }}</div>
        @endif
    </section>
</x-app-layout>
