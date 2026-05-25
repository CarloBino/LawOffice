<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <style>
            body { margin: 0; font-family: Figtree, ui-sans-serif, system-ui, sans-serif; background: #d1d2cd; color: #030203; }
            .hidden { display: none !important; }
            .block { display: block; }
            .flex { display: flex; }
            .inline-flex { display: inline-flex; }
            .grid { display: grid; }
            .items-center { align-items: center; }
            .justify-between { justify-content: space-between; }
            .mx-auto { margin-left: auto; margin-right: auto; }
            .w-full { width: 100%; }
            .min-h-screen { min-height: 100vh; }
            nav { background: #030203; border-bottom: 1px solid #554b45; }
            nav a, nav button { color: #fff; text-decoration: none; }
            nav button { font: inherit; }
            main a { color: inherit; }
            input, select, textarea { max-width: 100%; border: 1px solid #c1c1bd; background: #fff; padding: .65rem .8rem; }
            table { border-collapse: collapse; width: 100%; }
            th, td { padding: .9rem 1rem; border-bottom: 1px solid #d1d2cd; text-align: left; }
            @media (min-width: 640px) {
                .sm\:flex { display: flex !important; }
                .sm\:hidden { display: none !important; }
                .sm\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            }
            @media (min-width: 1024px) {
                .lg\:flex { display: flex !important; }
                .lg\:hidden { display: none !important; }
                .lg\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-[#030203]">
        <div class="min-h-screen bg-[#d1d2cd]">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-[#c1c1bd] bg-[#f4f2ed]">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot }}
                @endif
            </main>
        </div>
    </body>
</html>
