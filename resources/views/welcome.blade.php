<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="DFahMy Eco Resort is a peaceful nature retreat offering elegant stays, warm hospitality, and serene resort experiences.">
    <title>DFahMy Eco Resort | Nature Retreat & Premium Stay</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('brand/dfahmy-mark.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600;playfair+display:600,700&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f7f3ea] text-slate-900 antialiased fade-in-soft">
    @php
        $canAccessDashboard = auth()->user()?->can('dashboard.view') ?? false;
        $canManageBookings = auth()->user()?->can('bookings.manage') ?? false;
        $bookNowUrl = auth()->check()
            ? ($canManageBookings
                ? route('bookings.create')
                : route('guest.portal'))
            : (Route::has('register')
                ? route('register')
                : (Route::has('login')
                    ? route('login')
                    : '#contact'));
    @endphp

    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[110] focus:rounded-md focus:bg-[#1f3a2f] focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Skip to content
    </a>

    <div x-data="{ mobileOpen: false }" class="relative">
        <header class="sticky top-0 z-50 border-b border-[#d9cfbe] bg-[#f7f3ea]/95 backdrop-blur">
            <nav class="mx-auto flex h-24 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
                aria-label="Main">
                <a href="{{ url('/') }}" class="inline-flex items-center rounded-md px-1 py-1">
                    <img src="{{ asset('brand/dfahmy-logo-full.svg') }}" alt="D'FahMY ecogarden"
                        class="h-auto w-[168px] object-contain sm:w-[200px] lg:w-[220px]">
                </a>

                <div class="hidden items-center gap-8 text-sm font-medium text-[#2f4f3f] lg:flex">
                    <a href="#introduction" class="transition hover:text-[#1f3a2f]">About</a>
                    <a href="#accommodation" class="transition hover:text-[#1f3a2f]">Stays</a>
                    <a href="#experiences" class="transition hover:text-[#1f3a2f]">Experiences</a>
                    <a href="#gallery" class="transition hover:text-[#1f3a2f]">Gallery</a>
                    <a href="#contact" class="transition hover:text-[#1f3a2f]">Contact</a>
                </div>

                <div class="hidden items-center gap-3 lg:flex">
                    @if (Route::has('login'))
                        @auth
                            @if ($canAccessDashboard)
                                <a href="{{ route('dashboard') }}"
                                    class="rounded-md border border-[#cab89c] px-5 py-2 text-sm font-semibold text-[#214233] transition hover:bg-[#ece2d2]">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('profile.edit') }}"
                                    class="rounded-md border border-[#cab89c] px-5 py-2 text-sm font-semibold text-[#214233] transition hover:bg-[#ece2d2]">
                                    Profile
                                </a>
                                <a href="{{ route('guest.portal') }}"
                                    class="rounded-md bg-[#1f3a2f] px-5 py-2 text-sm font-semibold text-[#f6f1e8] shadow-sm transition hover:bg-[#183027]">
                                    Book Your Stay
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                                class="rounded-md border border-transparent px-4 py-2 text-sm font-semibold text-[#315241] transition hover:text-[#1f3a2f]">
                                Log In
                            </a>
                            <a href="{{ $bookNowUrl }}"
                                class="rounded-md bg-[#1f3a2f] px-5 py-2 text-sm font-semibold text-[#f6f1e8] shadow-sm transition hover:bg-[#183027]">
                                Book Your Stay
                            </a>
                        @endauth
                    @endif
                </div>

                <button type="button" @click="mobileOpen = !mobileOpen"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-md text-[#2f4f3f] transition hover:bg-[#ece3d4] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1f3a2f] lg:hidden"
                    :aria-expanded="mobileOpen.toString()" aria-controls="mobile-menu" aria-label="Toggle menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3.75 6.75h16.5m-16.5 5.25h16.5m-16.5 5.25h16.5" />
                        <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </nav>

            <div id="mobile-menu" x-show="mobileOpen" x-cloak x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-180"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="border-t border-[#d9cfbe] bg-[#f7f3ea] lg:hidden">
                <div class="space-y-1 px-4 py-4 text-sm font-medium text-[#2f4f3f]">
                    <a href="#introduction" @click="mobileOpen = false"
                        class="block rounded-md px-3 py-3 hover:bg-[#ece3d4] focus-visible:ring-2 focus-visible:ring-[#1f3a2f]">About</a>
                    <a href="#accommodation" @click="mobileOpen = false"
                        class="block rounded-md px-3 py-3 hover:bg-[#ece3d4] focus-visible:ring-2 focus-visible:ring-[#1f3a2f]">Stays</a>
                    <a href="#experiences" @click="mobileOpen = false"
                        class="block rounded-md px-3 py-3 hover:bg-[#ece3d4] focus-visible:ring-2 focus-visible:ring-[#1f3a2f]">Experiences</a>
                    <a href="#gallery" @click="mobileOpen = false"
                        class="block rounded-md px-3 py-3 hover:bg-[#ece3d4] focus-visible:ring-2 focus-visible:ring-[#1f3a2f]">Gallery</a>
                    <a href="#contact" @click="mobileOpen = false"
                        class="block rounded-md px-3 py-3 hover:bg-[#ece3d4] focus-visible:ring-2 focus-visible:ring-[#1f3a2f]">Contact</a>

                    @if (Route::has('login'))
                        @auth
                            @if ($canAccessDashboard)
                                <a href="{{ route('dashboard') }}"
                                    class="mt-2 block rounded-md border border-[#cdbda3] px-3 py-3 text-center text-[#214233] hover:bg-[#ece3d4] focus-visible:ring-2 focus-visible:ring-[#1f3a2f]">Dashboard</a>
                            @else
                                <a href="{{ route('profile.edit') }}"
                                    class="mt-2 block rounded-md border border-[#cdbda3] px-3 py-3 text-center text-[#214233] hover:bg-[#ece3d4] focus-visible:ring-2 focus-visible:ring-[#1f3a2f]">Profile</a>
                                <a href="{{ route('guest.portal') }}" @click="mobileOpen = false"
                                    class="mt-2 block rounded-md bg-[#1f3a2f] px-3 py-3 text-center text-[#f6f1e8] focus-visible:ring-2 focus-visible:ring-[#1f3a2f]">Book
                                    Your Stay</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                                class="mt-2 block rounded-md border border-[#cdbda3] px-3 py-3 text-center text-[#214233] hover:bg-[#ece3d4] focus-visible:ring-2 focus-visible:ring-[#1f3a2f]">Log
                                In</a>
                            <a href="{{ $bookNowUrl }}"
                                class="mt-2 block rounded-md bg-[#1f3a2f] px-3 py-3 text-center text-[#f6f1e8] focus-visible:ring-2 focus-visible:ring-[#1f3a2f]">Book
                                Your Stay</a>
                        @endauth
                    @endif
                </div>
            </div>
        </header>

        @if (session('status') === 'account-created')
            <div class="border-b border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-800"
                role="status">
                Your account is ready. Contact our team below to arrange your stay.
            </div>
        @endif

        <main id="main-content">
            <section class="relative min-h-[88vh] overflow-hidden">
                <img src="{{ asset('brand/dfahmy-resort-hero.jpg') }}"
                    alt="Aerial view of DFahMy Eco Resort surrounded by forest"
                    class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-b from-[#0e201a]/60 via-[#10241d]/58 to-[#10241d]/68">
                </div>

                <div class="relative mx-auto flex min-h-[88vh] max-w-7xl items-center px-4 sm:px-6 lg:px-8">
                    <div class="max-w-2xl text-[#f6f1e8] rise-in-soft">
                        <p class="text-sm uppercase tracking-[0.22em] text-[#e8dcc5]">Premium Nature Retreat</p>
                        <h1 class="mt-4 font-serif text-[2.1rem] leading-[1.1] sm:text-5xl lg:text-6xl">A calm stay in
                            the heart of green serenity.</h1>
                        <p class="mt-5 max-w-xl text-[0.97rem] leading-7 text-[#f2e9d8] sm:text-lg sm:leading-8">
                            Experience thoughtful hospitality, elegant spaces, and the gentle rhythm of nature at DFahMy
                            Eco Resort.</p>
                        <div class="mt-9 flex flex-wrap gap-3 rise-in-soft rise-in-delay-1">
                            <a href="{{ $bookNowUrl }}"
                                class="w-full rounded-md bg-[#1f3a2f] px-6 py-3 text-center text-sm font-semibold text-[#f6f1e8] shadow-lg shadow-[#0f211a]/20 transition hover:bg-[#183027] sm:w-auto">
                                Book Your Stay
                            </a>
                            <a href="#accommodation"
                                class="w-full rounded-md border border-[#e5d8c2] bg-[#f6f1e8]/15 px-6 py-3 text-center text-sm font-semibold text-[#f6f1e8] transition hover:bg-[#f6f1e8]/25 sm:w-auto">
                                Explore Stays
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section id="introduction" class="border-b border-[#ddd2c0] bg-[#f7f3ea] py-20 sm:py-24">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:gap-14 lg:px-8">
                    <div class="rise-in-soft">
                        <p class="text-sm uppercase tracking-[0.2em] text-[#6a826f]">Welcome to DFahMy</p>
                        <h2 class="mt-3 font-serif text-3xl text-[#1f3a2f] sm:text-4xl">A restorative eco-resort
                            crafted for stillness and comfort.</h2>
                    </div>
                    <p class="text-base leading-8 text-slate-700 rise-in-soft rise-in-delay-1">DFahMy Eco Resort
                        combines natural beauty with refined hospitality. From tranquil mornings beside lush greenery to
                        evenings under warm skies, every detail is designed for meaningful rest and mindful connection.
                    </p>
                </div>
            </section>

            <section id="accommodation" class="border-b border-[#ddd2c0] bg-[#f9f6ef] py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mb-10 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.2em] text-[#6a826f]">Accommodation Highlights</p>
                            <h2 class="mt-2 font-serif text-3xl text-[#1f3a2f] sm:text-4xl">Distinct stays for every
                                style of retreat.</h2>
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                        <article
                            class="overflow-hidden rounded-xl border border-[#dfd4c2] bg-[#fffdf9] shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md">
                            <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80"
                                alt="Main House exterior" class="h-44 w-full object-cover">
                            <div class="p-5">
                                <h3 class="font-serif text-xl text-[#1f3a2f]">Main House</h3>
                                <p class="mt-2 text-sm leading-7 text-slate-700">A warm central residence with spacious
                                    communal charm and timeless comfort.</p>
                            </div>
                        </article>

                        <article
                            class="overflow-hidden rounded-xl border border-[#dfd4c2] bg-[#fffdf9] shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md">
                            <img src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80"
                                alt="Pool House with tranquil water view" class="h-44 w-full object-cover">
                            <div class="p-5">
                                <h3 class="font-serif text-xl text-[#1f3a2f]">Pool House</h3>
                                <p class="mt-2 text-sm leading-7 text-slate-700">Relaxed suites beside calming water,
                                    ideal for unhurried mornings and sunsets.</p>
                            </div>
                        </article>

                        <article
                            class="overflow-hidden rounded-xl border border-[#dfd4c2] bg-[#fffdf9] shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md">
                            <img src="https://images.unsplash.com/photo-1502005097973-6a7082348e28?auto=format&fit=crop&w=900&q=80"
                                alt="Tebing House elevated among trees" class="h-44 w-full object-cover">
                            <div class="p-5">
                                <h3 class="font-serif text-xl text-[#1f3a2f]">Tebing House</h3>
                                <p class="mt-2 text-sm leading-7 text-slate-700">An elevated retreat with dramatic
                                    natural views and serene private corners.</p>
                            </div>
                        </article>

                        <article
                            class="overflow-hidden rounded-xl border border-[#dfd4c2] bg-[#fffdf9] shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md">
                            <img src="https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=900&q=80"
                                alt="Garden Suite with lush tropical plants" class="h-44 w-full object-cover">
                            <div class="p-5">
                                <h3 class="font-serif text-xl text-[#1f3a2f]">Garden Suite</h3>
                                <p class="mt-2 text-sm leading-7 text-slate-700">Intimate garden-facing accommodation
                                    for quiet rest and deeper renewal.</p>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <section id="experiences" class="border-b border-[#ddd2c0] bg-[#f7f3ea] py-20 sm:py-24">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:gap-14 lg:px-8">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-[#6a826f]">Resort Experiences</p>
                        <h2 class="mt-3 font-serif text-3xl text-[#1f3a2f] sm:text-4xl">Every stay is shaped by ease,
                            nature, and thoughtful service.</h2>
                        <p class="mt-5 text-base leading-8 text-slate-700">Enjoy curated experiences designed to slow
                            your pace and enrich your moments at the resort.</p>
                    </div>

                    <ul class="grid gap-4 sm:grid-cols-2">
                        <li
                            class="rounded-lg border border-[#ddcfb9] bg-[#fbf8f2] p-5 transition duration-300 hover:-translate-y-1 hover:shadow-sm">
                            <h3 class="font-semibold text-[#234434]">Forest Morning Walks</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-700">Gentle guided routes through nearby
                                greenery and fresh highland air.</p>
                        </li>
                        <li
                            class="rounded-lg border border-[#ddcfb9] bg-[#fbf8f2] p-5 transition duration-300 hover:-translate-y-1 hover:shadow-sm">
                            <h3 class="font-semibold text-[#234434]">Private Dining Options</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-700">Simple, refined meals inspired by local
                                flavors and seasonal ingredients.</p>
                        </li>
                        <li
                            class="rounded-lg border border-[#ddcfb9] bg-[#fbf8f2] p-5 transition duration-300 hover:-translate-y-1 hover:shadow-sm">
                            <h3 class="font-semibold text-[#234434]">Wellness Corners</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-700">Quiet nooks for reading, journaling,
                                meditation, or mindful reflection.</p>
                        </li>
                        <li
                            class="rounded-lg border border-[#ddcfb9] bg-[#fbf8f2] p-5 transition duration-300 hover:-translate-y-1 hover:shadow-sm">
                            <h3 class="font-semibold text-[#234434]">Family-Friendly Spaces</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-700">Comfortable shared areas that balance
                                togetherness and personal calm.</p>
                        </li>
                    </ul>
                </div>
            </section>

            <section id="gallery" class="border-b border-[#ddd2c0] bg-[#faf7f0] py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <p class="text-sm uppercase tracking-[0.2em] text-[#6a826f]">Calm Gallery</p>
                    <h2 class="mt-2 font-serif text-3xl text-[#1f3a2f] sm:text-4xl">A glimpse of your retreat
                        atmosphere.</h2>

                    <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=700&q=80"
                            alt="Misty forest landscape"
                            class="h-40 w-full rounded-lg border border-[#dfd4c2] object-cover shadow-sm sm:h-48">
                        <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=700&q=80"
                            alt="Resort interior with natural tones"
                            class="h-40 w-full rounded-lg border border-[#dfd4c2] object-cover shadow-sm sm:h-48">
                        <img src="https://images.unsplash.com/photo-1519823551278-64ac92734fb1?auto=format&fit=crop&w=700&q=80"
                            alt="Poolside greenery"
                            class="h-40 w-full rounded-lg border border-[#dfd4c2] object-cover shadow-sm sm:h-48">
                        <img src="https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=700&q=80"
                            alt="Calm bedroom with soft textures"
                            class="h-40 w-full rounded-lg border border-[#dfd4c2] object-cover shadow-sm sm:h-48">
                        <img src="https://images.unsplash.com/photo-1472396961693-142e6e269027?auto=format&fit=crop&w=700&q=80"
                            alt="Deer in a forest clearing"
                            class="h-40 w-full rounded-lg border border-[#dfd4c2] object-cover shadow-sm sm:h-48">
                        <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=700&q=80"
                            alt="Mountain and valley retreat view"
                            class="h-40 w-full rounded-lg border border-[#dfd4c2] object-cover shadow-sm sm:h-48">
                        <img src="https://images.unsplash.com/photo-1464146072230-91cabc968266?auto=format&fit=crop&w=700&q=80"
                            alt="Natural warm breakfast setting"
                            class="h-40 w-full rounded-lg border border-[#dfd4c2] object-cover shadow-sm sm:h-48">
                        <img src="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=700&q=80"
                            alt="Resort deck surrounded by trees"
                            class="h-40 w-full rounded-lg border border-[#dfd4c2] object-cover shadow-sm sm:h-48">
                    </div>
                </div>
            </section>

            <section class="border-b border-[#ddd2c0] bg-[#f7f3ea] py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <p class="text-sm uppercase tracking-[0.2em] text-[#6a826f]">Guest Reflections</p>
                    <h2 class="mt-2 font-serif text-3xl text-[#1f3a2f] sm:text-4xl">Kind words from recent stays.</h2>

                    <div class="mt-10 grid gap-6 lg:grid-cols-3">
                        <blockquote
                            class="rounded-lg border border-[#dfd4c2] bg-[#fbf8f2] p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md">
                            <p class="text-sm leading-7 text-slate-700">"The atmosphere was tranquil, the rooms felt
                                elegant yet grounded, and every staff interaction was warm and thoughtful."</p>
                            <footer class="mt-4 text-sm font-semibold text-[#214233]">Aina &amp; Hafiz, Kuala Lumpur
                            </footer>
                        </blockquote>
                        <blockquote
                            class="rounded-lg border border-[#dfd4c2] bg-[#fbf8f2] p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md">
                            <p class="text-sm leading-7 text-slate-700">"Perfect for a restorative weekend. The nature
                                views, clean design, and calm pacing made all the difference."</p>
                            <footer class="mt-4 text-sm font-semibold text-[#214233]">Noah T., Singapore</footer>
                        </blockquote>
                        <blockquote
                            class="rounded-lg border border-[#dfd4c2] bg-[#fbf8f2] p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md">
                            <p class="text-sm leading-7 text-slate-700">"A truly premium eco retreat. We loved the
                                peaceful mornings and the understated luxury throughout the resort."</p>
                            <footer class="mt-4 text-sm font-semibold text-[#214233]">Maya R., Penang</footer>
                        </blockquote>
                    </div>
                </div>
            </section>

            <section id="contact" class="bg-[#f9f5ed] py-20 sm:py-24">
                <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:gap-12 lg:px-8">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-[#6a826f]">Location &amp; Contact</p>
                        <h2 class="mt-2 font-serif text-3xl text-[#1f3a2f] sm:text-4xl">Find your way to a slower
                            rhythm.</h2>
                        <p class="mt-5 text-base leading-8 text-slate-700">DFahMy Eco Resort, Bentong Highlands,
                            Pahang, Malaysia. Reach out for stay availability, private bookings, and tailored retreat
                            arrangements.</p>
                    </div>

                    <div class="rounded-xl border border-[#ddcfb9] bg-[#fffdf9] p-6 shadow-sm">
                        <dl class="space-y-4 text-sm text-slate-700">
                            <div>
                                <dt class="font-semibold text-[#234434]">Phone</dt>
                                <dd>+60 12-345 6789</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-[#234434]">Email</dt>
                                <dd>stay@dfahmyecoresort.com</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-[#234434]">Address</dt>
                                <dd>Lot 88, Bentong Highlands, 28700 Bentong, Pahang</dd>
                            </div>
                        </dl>

                        <a href="{{ $bookNowUrl }}"
                            class="mt-6 inline-flex rounded-md bg-[#1f3a2f] px-5 py-3 text-sm font-semibold text-[#f6f1e8] transition hover:bg-[#183027]">
                            Book Your Stay
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-[#d9cfbe] bg-[#132922] text-[#ece3d2]">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-14 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
                <div>
                    <p class="text-sm tracking-[0.22em] text-[#d7c8ae]">DFAHMY ECO RESORT</p>
                    <p class="mt-3 text-sm leading-7 text-[#d9cfbe]">A refined eco retreat blending natural calm with
                        premium comfort.</p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-[#f2e8d5]">Navigation</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[#d9cfbe]">
                        <li><a href="#introduction" class="transition hover:text-white">About</a></li>
                        <li><a href="#accommodation" class="transition hover:text-white">Stays</a></li>
                        <li><a href="#experiences" class="transition hover:text-white">Experiences</a></li>
                        <li><a href="#contact" class="transition hover:text-white">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-[#f2e8d5]">Contact</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[#d9cfbe]">
                        <li>+60 12-345 6789</li>
                        <li>stay@dfahmyecoresort.com</li>
                        <li>Bentong Highlands, Pahang</li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-[#f2e8d5]">Social</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[#d9cfbe]">
                        <li><a href="#" class="transition hover:text-white">Instagram</a></li>
                        <li><a href="#" class="transition hover:text-white">Facebook</a></li>
                        <li><a href="#" class="transition hover:text-white">YouTube</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-[#27463a] px-4 py-4 text-center text-xs text-[#bcae95] sm:px-6 lg:px-8">
                &copy; {{ now()->year }} DFahMy Eco Resort. All rights reserved.
            </div>
        </footer>
    </div>
</body>

</html>
