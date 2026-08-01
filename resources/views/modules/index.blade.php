<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ $moduleName }}
        </h2>
    </x-slot>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">{{ $moduleName }}</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $description }}</p>

        <div class="mt-6 flex flex-wrap gap-3">
            @can('bookings.manage')
                @if ($moduleName === 'Bookings')
                    <button type="button"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Create Booking
                    </button>
                @endif
            @endcan

            @can('guests.manage')
                @if ($moduleName === 'Guests')
                    <button type="button"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Add Guest
                    </button>
                @endif
            @endcan

            @can('payments.manage')
                @if ($moduleName === 'Payments')
                    <button type="button"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Record Payment
                    </button>
                @endif
            @endcan

            @can('housekeeping.manage')
                @if ($moduleName === 'Housekeeping Management')
                    <button type="button"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Assign Task
                    </button>
                @endif
            @endcan

            @can('maintenance.manage')
                @if ($moduleName === 'Maintenance')
                    <button type="button"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Create Work Order
                    </button>
                @endif
            @endcan

            @can('reports.view')
                @if ($moduleName === 'Reports')
                    <button type="button"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Generate Report
                    </button>
                @endif
            @endcan
        </div>
    </section>
</x-app-layout>
