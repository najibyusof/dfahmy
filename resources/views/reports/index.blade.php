<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">Reports</h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="from" class="block text-xs font-semibold text-slate-600">From</label>
                <input id="from" type="date" name="from" value="{{ $filters['from'] }}"
                    class="mt-1 rounded-lg border-slate-300 text-sm">
            </div>
            <div>
                <label for="to" class="block text-xs font-semibold text-slate-600">To</label>
                <input id="to" type="date" name="to" value="{{ $filters['to'] }}"
                    class="mt-1 rounded-lg border-slate-300 text-sm">
            </div>
            <button type="submit"
                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Apply</button>
        </form>
    </section>

    <section class="mt-5 space-y-5">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900">Occupancy by Building</h3>
                <a href="{{ route('reports.export', ['type' => 'occupancy_by_building', 'from' => $filters['from'], 'to' => $filters['to']]) }}"
                    class="text-xs font-semibold text-emerald-700">Export CSV</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Building</th>
                            <th class="px-3 py-2">Rooms</th>
                            <th class="px-3 py-2">Booked Nights</th>
                            <th class="px-3 py-2">Total Room Nights</th>
                            <th class="px-3 py-2">Occupancy %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($reports['occupancy_by_building'] as $row)
                            <tr>
                                <td class="px-3 py-2">{{ $row['building'] }}</td>
                                <td class="px-3 py-2">{{ $row['rooms'] }}</td>
                                <td class="px-3 py-2">{{ $row['booked_nights'] }}</td>
                                <td class="px-3 py-2">{{ $row['total_room_nights'] }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $row['occupancy_rate_percent'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-3 text-slate-500">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900">Booking Source Summary</h3><a
                    href="{{ route('reports.export', ['type' => 'booking_source_summary', 'from' => $filters['from'], 'to' => $filters['to']]) }}"
                    class="text-xs font-semibold text-emerald-700">Export CSV</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Source</th>
                            <th class="px-3 py-2">Bookings</th>
                            <th class="px-3 py-2">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($reports['booking_source_summary'] as $row)
                            <tr>
                                <td class="px-3 py-2">{{ $row['booking_source'] }}</td>
                                <td class="px-3 py-2">{{ $row['total_bookings'] }}</td>
                                <td class="px-3 py-2">RM {{ number_format((float) $row['total_amount'], 2) }}</td>
                        </tr>@empty<tr>
                                <td colspan="3" class="px-3 py-3 text-slate-500">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <div class="grid gap-5 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Revenue Summary</h3><a
                        href="{{ route('reports.export', ['type' => 'revenue_summary', 'from' => $filters['from'], 'to' => $filters['to']]) }}"
                        class="text-xs font-semibold text-emerald-700">Export CSV</a>
                </div>
                <dl class="space-y-2 text-sm text-slate-700">
                    <div class="flex justify-between">
                        <dt>Paid Amount</dt>
                        <dd>RM {{ number_format((float) $reports['revenue_summary']['paid_amount'], 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Refunded Amount</dt>
                        <dd>RM {{ number_format((float) $reports['revenue_summary']['refunded_amount'], 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Pending Amount</dt>
                        <dd>RM {{ number_format((float) $reports['revenue_summary']['pending_amount'], 2) }}</dd>
                    </div>
                    <div class="flex justify-between font-semibold text-slate-900">
                        <dt>Net Collected</dt>
                        <dd>RM {{ number_format((float) $reports['revenue_summary']['net_collected'], 2) }}</dd>
                    </div>
                </dl>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Payment Method Summary</h3><a
                        href="{{ route('reports.export', ['type' => 'payment_method_summary', 'from' => $filters['from'], 'to' => $filters['to']]) }}"
                        class="text-xs font-semibold text-emerald-700">Export CSV</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-3 py-2">Method</th>
                                <th class="px-3 py-2">Transactions</th>
                                <th class="px-3 py-2">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($reports['payment_method_summary'] as $row)
                                <tr>
                                    <td class="px-3 py-2">{{ str_replace('_', ' ', $row['payment_method']) }}</td>
                                    <td class="px-3 py-2">{{ $row['total_transactions'] }}</td>
                                    <td class="px-3 py-2">RM {{ number_format((float) $row['total_amount'], 2) }}</td>
                            </tr>@empty<tr>
                                    <td colspan="3" class="px-3 py-3 text-slate-500">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900">Outstanding Balance Report</h3><a
                    href="{{ route('reports.export', ['type' => 'outstanding_balance_report', 'from' => $filters['from'], 'to' => $filters['to']]) }}"
                    class="text-xs font-semibold text-emerald-700">Export CSV</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Booking Ref</th>
                            <th class="px-3 py-2">Guest</th>
                            <th class="px-3 py-2">Check-In</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Total</th>
                            <th class="px-3 py-2">Outstanding</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($reports['outstanding_balance_report'] as $row)
                            <tr>
                                <td class="px-3 py-2">{{ $row['booking_reference'] }}</td>
                                <td class="px-3 py-2">{{ $row['guest'] }}</td>
                                <td class="px-3 py-2">{{ $row['check_in_date'] }}</td>
                                <td class="px-3 py-2">{{ $row['booking_status'] }}</td>
                                <td class="px-3 py-2">RM {{ number_format((float) $row['total_amount'], 2) }}</td>
                                <td class="px-3 py-2 font-semibold text-rose-700">RM
                                    {{ number_format((float) $row['outstanding_balance'], 2) }}</td>
                        </tr>@empty<tr>
                                <td colspan="6" class="px-3 py-3 text-slate-500">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <div class="grid gap-5 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Housekeeping Report</h3><a
                        href="{{ route('reports.export', ['type' => 'housekeeping_report', 'from' => $filters['from'], 'to' => $filters['to']]) }}"
                        class="text-xs font-semibold text-emerald-700">Export CSV</a>
                </div>
                <dl class="space-y-2 text-sm text-slate-700">
                    @foreach ($reports['housekeeping_report'] as $label => $value)
                        <div class="flex justify-between">
                            <dt>{{ str_replace('_', ' ', $label) }}</dt>
                            <dd>{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Maintenance Report</h3><a
                        href="{{ route('reports.export', ['type' => 'maintenance_report', 'from' => $filters['from'], 'to' => $filters['to']]) }}"
                        class="text-xs font-semibold text-emerald-700">Export CSV</a>
                </div>
                <dl class="space-y-2 text-sm text-slate-700">
                    @foreach ($reports['maintenance_report'] as $label => $value)
                        <div class="flex justify-between">
                            <dt>{{ str_replace('_', ' ', $label) }}</dt>
                            <dd>{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </article>
        </div>
    </section>
</x-app-layout>
