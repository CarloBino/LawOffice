<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Law Office') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-[#030203] antialiased">
        <main class="min-h-screen bg-[#030203] lg:grid lg:grid-cols-[1.08fr_0.92fr]">
            <section class="relative hidden min-h-screen overflow-hidden lg:block">
                <img
                    src="{{ asset('images/law-office-hero.png') }}"
                    alt="Law office desk prepared for casework"
                    class="absolute inset-0 h-full w-full object-cover"
                >
                <div class="absolute inset-0 bg-[#030203]/65"></div>

                <div class="relative z-10 flex min-h-screen flex-col justify-between p-12 xl:p-16">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 text-white">
                        <span class="flex h-12 w-12 items-center justify-center border border-white/30 bg-black/25 text-sm font-extrabold">LO</span>
                        <span class="text-xl font-bold">{{ config('app.name', 'Law Office') }}</span>
                    </a>

                    <div class="max-w-xl pb-10 text-white">
                        <p class="text-xs font-bold uppercase text-[#c7a47b]">Secure practice workspace</p>
                        <h1 class="mt-4 text-5xl font-extrabold leading-tight xl:text-6xl">Your matters, schedules, and records in one place.</h1>
                        <p class="mt-5 max-w-lg text-base leading-7 text-[#e3e3df]">Authorized personnel can securely access client records, active cases, hearings, billing, and office documents.</p>
                    </div>

                    <p class="text-xs font-semibold uppercase text-white/65">Confidential office access</p>
                </div>
            </section>

            <section class="flex min-h-screen flex-col bg-[#f4f2ed]">
                <div class="flex items-center justify-between border-b border-[#d1d2cd] px-5 py-5 sm:px-8 lg:hidden">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center bg-[#030203] text-xs font-extrabold text-white">LO</span>
                        <span class="font-bold">{{ config('app.name', 'Law Office') }}</span>
                    </a>
                    <a href="{{ url('/') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Home</a>
                </div>

                <div class="flex flex-1 items-center justify-center px-5 py-10 sm:px-8 lg:px-12">
                    <div class="w-full max-w-md">
                        {{ $slot }}
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
