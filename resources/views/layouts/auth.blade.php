<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' | ' . config('app.name', 'AlumniHub') : config('app.name', 'AlumniHub') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/alumnihub-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/alumnihub-logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen flex">

        {{-- ── Left branding panel (desktop only) ─────────────────────────────── --}}
        <div class="hidden lg:flex flex-col justify-between w-5/12 bg-red-900 px-12 py-10 relative overflow-hidden">
            {{-- Background texture --}}
            <div class="absolute inset-0 opacity-10"
                style="background-image: url('{{ asset('images/element.png') }}'); background-size: cover; background-position: center;"></div>

            <div class="relative z-10">
                <a href="/" class="flex items-center gap-3">
                    <img src="{{ asset('images/alumnihub-logo.png') }}" alt="AlumniHub" class="h-10 w-10 brightness-0 invert" />
                    <span class="text-2xl font-bold text-white tracking-tight">
                        Alumni<span class="text-yellow-400">Hub</span>
                    </span>
                </a>

                <div class="mt-16">
                    <h2 class="text-3xl font-bold text-white leading-snug">
                        Stay connected<br>with your PUP-ITECH<br>alumni community.
                    </h2>
                    <p class="mt-5 text-red-200 text-sm leading-relaxed max-w-xs">
                        Share experiences, grow your network, and discover career opportunities — all in one place built for PUP-ITECH graduates.
                    </p>
                </div>

                <div class="mt-12 flex flex-col gap-3">
                    <div class="flex items-center gap-3 text-red-100 text-sm">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-white/10">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        Alumni profiles &amp; connections
                    </div>
                    <div class="flex items-center gap-3 text-red-100 text-sm">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-white/10">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        Communities &amp; discussion boards
                    </div>
                    <div class="flex items-center gap-3 text-red-100 text-sm">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-white/10">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6a4 4 0 11-8 0 4 4 0 018 0zm-4 6v.01" />
                            </svg>
                        </div>
                        Career growth &amp; opportunities
                    </div>
                </div>
            </div>

            <p class="relative z-10 text-red-300/70 text-xs">© {{ date('Y') }} AlumniHub · PUP-ITECH</p>
        </div>

        {{-- ── Right form panel ─────────────────────────────────────────────────── --}}
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-12 bg-white overflow-y-auto">
            {{-- Mobile logo --}}
            <a href="/" class="lg:hidden flex items-center gap-2 mb-8">
                <img src="{{ asset('images/alumnihub-logo.png') }}" alt="AlumniHub" class="h-8 w-8" />
                <span class="text-xl font-bold tracking-tight">
                    Alumni<span class="text-yellow-500">Hub</span>
                </span>
            </a>

            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>

    </div>
</body>

</html>
