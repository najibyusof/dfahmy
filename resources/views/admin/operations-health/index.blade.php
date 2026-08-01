<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Operations Health</h2>
    </x-slot>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Failed Jobs</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $failedJobsCount }}</p>
            <p class="mt-1 text-sm text-slate-600">Last 24h: {{ $failedJobsLast24Hours }}</p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Pending Jobs</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $pendingJobsCount }}</p>
            <p class="mt-1 text-sm text-slate-600">
                @if ($oldestPendingAgeMinutes === null)
                    Oldest pending age: n/a
                @else
                    Oldest pending age: {{ $oldestPendingAgeMinutes }} min
                @endif
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Scheduler Heartbeat</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">
                @if ($heartbeatAt)
                    {{ $heartbeatAgeMinutes }} min
                @else
                    n/a
                @endif
            </p>
            <p class="mt-1 text-sm text-slate-600">
                @if ($heartbeatAt)
                    Last: {{ $heartbeatAt->format('Y-m-d H:i:s') }}
                @else
                    No heartbeat yet
                @endif
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase text-slate-500">Integrations</p>
            <p class="mt-2 text-sm text-slate-700">Queue: <span
                    class="font-semibold text-slate-900">{{ $queueConnection }}</span></p>
            <p class="mt-1 text-sm text-slate-700">Failed driver: <span
                    class="font-semibold text-slate-900">{{ $queueFailedDriver }}</span></p>
            <p class="mt-1 text-sm text-slate-700">Mailer: <span
                    class="font-semibold text-slate-900">{{ $mailer }}</span></p>
            <p class="mt-1 text-sm text-slate-700">Telegram:
                <span class="font-semibold {{ $telegramConfigured ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $telegramConfigured ? 'Configured' : 'Missing config' }}
                </span>
            </p>
        </article>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-base font-semibold text-slate-900">Operational Checks</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Check</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ($checks as $check)
                        <tr>
                            <td class="px-3 py-2 font-medium text-slate-900">{{ $check['name'] }}</td>
                            <td class="px-3 py-2">
                                @php($status = $check['status'])
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                    {{ $status === 'healthy' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $status === 'warning' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $status === 'critical' ? 'bg-rose-100 text-rose-800' : '' }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="px-3 py-2">{{ $check['details'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
