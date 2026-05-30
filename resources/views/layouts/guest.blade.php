<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' | ' . config('app.name', 'AlumniHub') : config('app.name', 'AlumniHub') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/alumnihub-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/alumnihub-logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-[#1b1b18] antialiased min-h-screen flex flex-col items-center bg-[#FDFDFC]">
    <x-header />
    <div class="bg-cover bg-center flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0 pt-8 md:pt-20 px-8 pb-24"
        style="background-image: url('{{ asset('images/element.png') }}');">
        <!-- <div>
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </div> -->

        <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden rounded-lg">
            {{ $slot }}
        </div>
    </div>
    <x-footer />
</body>

</html>