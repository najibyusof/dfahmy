<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DFahMy Eco Resort') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="font-sans antialiased bg-slate-100 text-slate-900">
    @php($unreadNotificationCount = auth()->user()?->unreadNotifications()->count() ?? 0)
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-md focus:bg-emerald-700 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Skip to main content
    </a>

    <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen lg:flex">
        <aside class="hidden lg:flex lg:w-72 lg:flex-col lg:justify-between lg:border-r lg:border-slate-200 lg:bg-white"
            aria-label="Primary">
            <div>
                <div class="border-b border-slate-200 px-6 py-5">
                    <a href="{{ route('dashboard') }}" class="text-xl font-semibold tracking-tight text-emerald-700">
                        DFahMy Eco Resort
                    </a>
                </div>

                <nav class="space-y-2 px-4 py-6 text-sm font-medium" aria-label="Main navigation">
                    <a href="{{ route('dashboard') }}"
                        class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        Dashboard
                    </a>
                    @can('rooms.manage')
                        <a href="{{ route('buildings.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('buildings.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Buildings
                        </a>
                        <a href="{{ route('rooms.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('rooms.*') || request()->routeIs('modules.rooms.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Rooms
                        </a>
                        <a href="{{ route('room-bed-configurations.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('room-bed-configurations.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Room Beds
                        </a>
                    @endcan
                    @can('bookings.manage')
                        <a href="{{ route('bookings.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('bookings.*') || request()->routeIs('modules.bookings.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Bookings
                        </a>
                    @endcan
                    @can('guests.manage')
                        <a href="{{ route('guests.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('guests.*') || request()->routeIs('modules.guests.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Guests
                        </a>
                    @endcan
                    @can('payments.manage')
                        <a href="{{ route('payments.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('payments.*') || request()->routeIs('modules.payments.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Payments
                        </a>
                    @endcan
                    @can('checkin_checkout.manage')
                        <a href="{{ route('modules.checkin-checkout.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('modules.checkin-checkout.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Check-In / Check-Out
                        </a>
                    @endcan
                    @can('housekeeping.manage')
                        <a href="{{ route('modules.housekeeping.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('modules.housekeeping.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Housekeeping
                        </a>
                    @endcan
                    @can('housekeeping.assigned.view')
                        <a href="{{ route('housekeeping.tasks.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('housekeeping.tasks.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            My Housekeeping Tasks
                        </a>
                    @endcan
                    @can('maintenance.manage')
                        <a href="{{ route('modules.maintenance.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('modules.maintenance.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Maintenance
                        </a>
                    @endcan
                    @can('reports.view')
                        <a href="{{ route('modules.reports.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('modules.reports.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Reports
                        </a>
                    @endcan
                    @if (auth()->user()?->hasRole('Super Admin') || auth()->user()?->hasRole('Manager'))
                        <a href="{{ route('audit-logs.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('audit-logs.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Audit Logs
                        </a>
                    @endif
                    @can('users.manage')
                        <a href="{{ route('admin.users.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('admin.users.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            User Management
                        </a>
                        <a href="{{ route('admin.roles-matrix.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('admin.roles-matrix.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Roles Matrix
                        </a>
                        <a href="{{ route('admin.operations-health.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('admin.operations-health.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Operations Health
                        </a>
                        @if (auth()->user()?->hasRole('Super Admin'))
                            <a href="{{ route('admin.telegram-alert-settings.index') }}"
                                class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('admin.telegram-alert-settings.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                Telegram Alerts
                            </a>
                        @endif
                    @endcan
                    <a href="{{ route('profile.edit') }}"
                        class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('profile.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        Profile
                    </a>
                </nav>
            </div>

            <div class="border-t border-slate-200 px-6 py-5">
                <p class="text-sm font-medium text-slate-900">{{ Auth::user()->name }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ Auth::user()->email }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                        Log Out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <button @click="sidebarOpen = true"
                        class="inline-flex items-center justify-center rounded-md p-2 text-slate-700 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2 lg:hidden"
                        type="button">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3.75 6.75h16.5m-16.5 5.25h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    @isset($header)
                        <div class="text-lg font-semibold text-slate-900">
                            {{ $header }}
                        </div>
                    @else
                        <div class="text-lg font-semibold text-slate-900">Dashboard</div>
                    @endisset

                    <div class="flex items-center gap-3">
                        <a href="{{ route('notifications.index') }}"
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                            aria-label="Notifications">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M14.857 17.082a23.848 23.848 0 0 1 5.454 1.31A8.967 8.967 0 0 1 18 9.75v-.7V9A6 6 0 0 0 6 9v.05c0 .233 0 .467-.002.7a8.967 8.967 0 0 1-2.312 8.642 23.848 23.848 0 0 1 5.454-1.31m5.717 0a24.255 24.255 0 0 0-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            @if ($unreadNotificationCount > 0)
                                <span
                                    class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">
                                    {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                </span>
                            @endif
                        </a>
                        <div class="text-right">
                            <p class="text-sm font-medium text-slate-900">{{ Auth::user()->name }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                <div id="main-content" class="mx-auto max-w-7xl" tabindex="-1">
                    @if (session('status'))
                        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                            role="status" aria-live="polite">
                            {{ \Illuminate\Support\Str::of((string) session('status'))->replace('-', ' ')->headline() }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                            role="alert" aria-live="assertive">
                            <p class="font-semibold">Please fix the highlighted fields.</p>
                            <ul class="mt-2 list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>

        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden" aria-modal="true" role="dialog"
            aria-label="Mobile menu">
            <div class="absolute inset-0 bg-slate-900/50" @click="sidebarOpen = false"></div>
            <aside class="relative h-full w-72 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                    <a href="{{ route('dashboard') }}" class="text-xl font-semibold tracking-tight text-emerald-700">
                        DFahMy Eco Resort
                    </a>
                    <button @click="sidebarOpen = false" type="button"
                        class="rounded-md p-2 text-slate-700 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                        <span class="sr-only">Close sidebar</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="space-y-2 px-4 py-6 text-sm font-medium" aria-label="Mobile navigation">
                    <a href="{{ route('dashboard') }}"
                        class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        Dashboard
                    </a>
                    @can('rooms.manage')
                        <a href="{{ route('buildings.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('buildings.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Buildings
                        </a>
                        <a href="{{ route('rooms.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('rooms.*') || request()->routeIs('modules.rooms.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Rooms
                        </a>
                        <a href="{{ route('room-bed-configurations.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('room-bed-configurations.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Room Beds
                        </a>
                    @endcan
                    @can('bookings.manage')
                        <a href="{{ route('bookings.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('bookings.*') || request()->routeIs('modules.bookings.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Bookings
                        </a>
                    @endcan
                    @can('guests.manage')
                        <a href="{{ route('guests.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('guests.*') || request()->routeIs('modules.guests.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Guests
                        </a>
                    @endcan
                    @can('payments.manage')
                        <a href="{{ route('payments.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('payments.*') || request()->routeIs('modules.payments.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Payments
                        </a>
                    @endcan
                    @can('checkin_checkout.manage')
                        <a href="{{ route('modules.checkin-checkout.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('modules.checkin-checkout.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Check-In / Check-Out
                        </a>
                    @endcan
                    @can('housekeeping.manage')
                        <a href="{{ route('modules.housekeeping.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('modules.housekeeping.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Housekeeping
                        </a>
                    @endcan
                    @can('housekeeping.assigned.view')
                        <a href="{{ route('housekeeping.tasks.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('housekeeping.tasks.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            My Housekeeping Tasks
                        </a>
                    @endcan
                    @can('maintenance.manage')
                        <a href="{{ route('modules.maintenance.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('modules.maintenance.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Maintenance
                        </a>
                    @endcan
                    @can('reports.view')
                        <a href="{{ route('modules.reports.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('modules.reports.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Reports
                        </a>
                    @endcan
                    @if (auth()->user()?->hasRole('Super Admin') || auth()->user()?->hasRole('Manager'))
                        <a href="{{ route('audit-logs.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('audit-logs.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Audit Logs
                        </a>
                    @endif
                    @can('users.manage')
                        <a href="{{ route('admin.users.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('admin.users.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            User Management
                        </a>
                        <a href="{{ route('admin.roles-matrix.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('admin.roles-matrix.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Roles Matrix
                        </a>
                        <a href="{{ route('admin.operations-health.index') }}"
                            class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('admin.operations-health.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Operations Health
                        </a>
                        @if (auth()->user()?->hasRole('Super Admin'))
                            <a href="{{ route('admin.telegram-alert-settings.index') }}"
                                class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('admin.telegram-alert-settings.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                Telegram Alerts
                            </a>
                        @endif
                    @endcan
                    <a href="{{ route('profile.edit') }}"
                        class="block rounded-lg px-4 py-2.5 transition {{ request()->routeIs('profile.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        Profile
                    </a>
                </nav>

                <div class="border-t border-slate-200 px-6 py-5">
                    <p class="text-sm font-medium text-slate-900">{{ Auth::user()->name }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ Auth::user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                            Log Out
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</body>

</html>
