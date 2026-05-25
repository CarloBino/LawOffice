<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Law Office') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-[#d1d2cd] text-[#030203]">
        <div class="min-h-screen">
            <header class="absolute inset-x-0 top-0 z-20">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 text-white">
                        <span class="flex h-11 w-11 items-center justify-center border border-white/35 bg-white/10 text-sm font-bold backdrop-blur">LO</span>
                        <span class="text-lg font-semibold">{{ config('app.name', 'Law Office') }}</span>
                    </a>

                    <nav class="flex items-center gap-2 text-sm font-semibold text-white">
                        @auth
                            <a href="{{ route('dashboard') }}" class="border border-white/35 px-4 py-2 transition hover:bg-white hover:text-[#030203]">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="px-3 py-2 transition hover:text-[#c7a47b]">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-[#c7a47b] px-4 py-2 text-[#030203] transition hover:bg-white">Create account</a>
                            @endif
                        @endauth
                    </nav>
                </div>
            </header>

            <main>
                <section class="relative min-h-[82vh] overflow-hidden bg-[#030203]">
                    <img
                        src="{{ asset('images/law-office-hero.png') }}"
                        alt="A refined law office table with case documents and books"
                        class="absolute inset-0 h-full w-full object-cover"
                    >
                    <div class="absolute inset-0 bg-gradient-to-r from-[#030203] via-[#030203]/82 to-[#030203]/18"></div>
                    <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-[#d1d2cd] to-transparent"></div>

                    <div class="relative z-10 mx-auto flex min-h-[82vh] max-w-7xl items-center px-5 pb-14 pt-28 sm:px-8 lg:pt-32">
                        <div class="max-w-3xl text-white">
                            <p class="mb-5 inline-flex border border-white/25 px-4 py-2 text-xs font-bold uppercase text-[#d1d2cd]">
                                Counsel, casework, and court schedules
                            </p>
                            <h1 class="text-4xl font-extrabold leading-tight sm:text-6xl lg:text-7xl">
                                Strategic clarity for every matter on your desk.
                            </h1>
                            <p class="mt-6 max-w-2xl text-lg leading-8 text-[#d1d2cd]">
                                A focused law office workspace for managing clients, active cases, hearings, documents, billing, and opposing parties without losing the thread of the work.
                            </p>
                            <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center bg-[#c7a47b] px-6 py-3 text-sm font-bold text-[#030203] transition hover:bg-white">Open dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center bg-[#c7a47b] px-6 py-3 text-sm font-bold text-[#030203] transition hover:bg-white">Log in to workspace</a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center border border-white/35 px-6 py-3 text-sm font-bold text-white transition hover:bg-white hover:text-[#030203]">Create account</a>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                </section>

                <section class="relative bg-[#d1d2cd] px-5 py-12 sm:px-8">
                    <div class="mx-auto grid max-w-7xl gap-4 sm:grid-cols-3">
                        <div class="border border-[#9f7957]/35 bg-[#030203] p-6 text-white">
                            <p class="text-4xl font-extrabold">124</p>
                            <p class="mt-2 text-sm font-semibold uppercase text-[#c7a47b]">Client records</p>
                        </div>
                        <div class="border border-[#9f7957]/35 bg-[#554b45] p-6 text-white">
                            <p class="text-4xl font-extrabold">37</p>
                            <p class="mt-2 text-sm font-semibold uppercase text-[#d1d2cd]">Active matters</p>
                        </div>
                        <div class="border border-[#9f7957]/35 bg-white p-6 text-[#030203]">
                            <p class="text-4xl font-extrabold">214</p>
                            <p class="mt-2 text-sm font-semibold uppercase text-[#9f7957]">Filed documents</p>
                        </div>
                    </div>
                </section>

                <section class="bg-[#f4f2ed] px-5 py-16 sm:px-8">
                    <div class="mx-auto max-w-7xl">
                        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
                            <div>
                                <p class="text-sm font-bold uppercase text-[#9f7957]">Practice operations</p>
                                <h2 class="mt-3 text-3xl font-extrabold leading-tight text-[#030203] sm:text-4xl">Built around the rhythm of a working firm.</h2>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="border border-[#d1d2cd] bg-white p-6">
                                    <p class="font-bold text-[#030203]">Matter tracking</p>
                                    <p class="mt-3 text-sm leading-6 text-[#554b45]">Follow case status, priority, assigned counsel, and related parties from one organized record.</p>
                                </div>
                                <div class="border border-[#d1d2cd] bg-white p-6">
                                    <p class="font-bold text-[#030203]">Hearing calendar</p>
                                    <p class="mt-3 text-sm leading-6 text-[#554b45]">Keep court venue, branch, purpose, judge, and schedule details close to daily work.</p>
                                </div>
                                <div class="border border-[#d1d2cd] bg-white p-6">
                                    <p class="font-bold text-[#030203]">Document control</p>
                                    <p class="mt-3 text-sm leading-6 text-[#554b45]">Attach pleadings, contracts, and evidence files to the matters they belong to.</p>
                                </div>
                                <div class="border border-[#d1d2cd] bg-white p-6">
                                    <p class="font-bold text-[#030203]">Billing visibility</p>
                                    <p class="mt-3 text-sm leading-6 text-[#554b45]">Review fees, filing costs, payments, receipts, and outstanding balances quickly.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
