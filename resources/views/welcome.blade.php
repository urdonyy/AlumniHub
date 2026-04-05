<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AlumniHub') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset("css/tailwind.min.css") }}">
    @endif
</head>

<body class="bg-[#FDFDFC] text-[#1b1b18] flex items-center lg:justify-center min-h-screen flex-col">
    <x-header />

    <div class="bg-cover bg-center flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0 pt-8 lg:pt-20 px-8 pb-24"
        style="background-image: url('{{ asset('images/element.png') }}');">
        <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row rounded-2xl shadow-md">
            <div
                class="text-[13px] leading-[20px] flex-1 p-6 pb-6 lg:p-20 lg:pb-10 bg-white dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">
                <h2 class="mb-1 font-medium text-xl md:text-2xl lg:text-3xl relative inline-block">
                    <span class="relative z-10 text-[#4a4a4a]">
                        What is <span class="text-red-900">Alumni</span><span class="text-[#FFC107]">Hub</span>?
                    </span>
                    <img src="{{ asset('images/paint-stroke.png') }}" alt="paint stroke"
                        class="absolute left-0 bottom-0 w-full h-auto z-0 pointer-events-none" />
                </h2>
                <p class="mb-2 text-gray-600 lg:text-base">AlumniHub is a web-based platform designed to connect
                    graduates of the Polytechnic University of the Philippines – Institute of Technology (PUP ITECH)
                    with their alma mater and fellow alumni.</p>
                <ul class="flex gap-3 text-sm leading-normal mt-8">
                    <li>
                        <a href="/about" target="_blank"
                            class="flex items-center justify-center gap-2 sm:gap-4 bg-red-900 border-red-900 text-white font-medium hover:bg-[#FFC107] hover:border-[#FFC107] hover:text-red-900 px-5 py-1.5 rounded-sm border text-sm leading-normal whitespace-nowrap">
                            Learn More
                            <svg width="18" height="10" viewBox="0 0 20 13" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M0.000139952 7.36387L0 5.36397H16.1719L12.2222 1.41421L13.6364 0L20.0004 6.36397L13.6364 12.728L12.2222 11.3137L16.172 7.36397L0.000139952 7.36387Z"
                                    fill="currentColor" />
                            </svg>
                        </a>
                    </li>
                </ul>

            </div>

            <!-- peeps -->
            <div
                class="bg-white relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-tl-none lg:rounded-r-lg aspect-[335/364] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">
                <img src="{{ asset('images/people.png') }}" alt="People"
                    class="bg-cover bg-center h-full w-full absolute inset-0 object-cover rounded-2xl lg:rounded-bl-full rounded-b-none" />
            </div>
        </main>
    </div>

    <x-footer />
    @if (Route::has('login'))
        <div class="h-14.5 hidden lg:block"></div>
    @endif
</body>

</html>