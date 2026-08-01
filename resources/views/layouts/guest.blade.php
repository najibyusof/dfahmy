<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="Access your DFahMy Eco Resort account to manage your stay in a calm and seamless experience.">

    <title>{{ config('app.name', 'DFahMy Eco Resort') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('brand/dfahmy-mark.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600;playfair+display:600,700&display=swap"
        rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f5f1e8] font-sans text-slate-900 antialiased fade-in-soft">
    <div class="grid min-h-screen lg:grid-cols-2">
        <aside
            class="relative hidden overflow-hidden border-r border-[#d7cfbf] bg-[#1f3a2f] text-[#f8f4ea] lg:flex lg:flex-col lg:justify-between">
            <img src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1600&q=80"
                alt="Tropical forest landscape near a retreat"
                class="absolute inset-0 h-full w-full object-cover opacity-30">

            <div class="relative z-10 p-10 xl:p-14">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('brand/dfahmy-logo-full.svg') }}" alt="D'FahMY ecogarden"
                        class="h-14 w-auto max-w-[14rem]">
                </a>
            </div>

            <div class="relative z-10 px-10 pb-14 rise-in-soft xl:px-14 xl:pb-16">
                <h1 class="max-w-md font-serif text-4xl leading-tight text-[#f8f4ea] xl:text-5xl">Where nature quiets
                    the mind.</h1>
                <p class="mt-5 max-w-md text-sm leading-7 text-[#e7dcc8] xl:text-base">Sign in to continue your retreat
                    journey with calm, comfort, and effortless hospitality.</p>
            </div>
        </aside>

        <main class="flex items-center justify-center px-4 py-8 sm:px-8 sm:py-10 lg:px-12">
            <section
                class="w-full max-w-md rounded-2xl border border-[#d8cfbf] bg-[#fbf8f2] p-5 shadow-[0_16px_50px_rgba(31,58,47,0.08)] rise-in-soft rise-in-delay-1 sm:p-8">
                <div class="mb-6">
                    <a href="{{ url('/') }}"
                        class="inline-flex min-h-11 items-center gap-2 rounded-md px-2 text-sm font-medium text-[#2f4f3f] transition hover:bg-[#ece3d4] hover:text-[#1f3a2f] focus-visible:ring-2 focus-visible:ring-[#1f3a2f]">
                        <span aria-hidden="true">&larr;</span>
                        Back to DFahMy Eco Resort
                    </a>
                </div>

                {{ $slot }}
            </section>
        </main>
    </div>
</body>

</html>
