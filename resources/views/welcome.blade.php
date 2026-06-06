<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AlumniHub') }} — Reconnect with your alumni community</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/alumnihub-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/alumnihub-logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset("css/tailwind.min.css") }}">
    @endif
</head>

<body class="bg-[#FDFDFC] text-[#1b1b18] flex items-center lg:justify-center min-h-screen flex-col">
    <x-header />

    <!-- para__hero -->
    <section id="parallax-hero"
        class="relative w-full h-full min-h-[100vh] flex items-center justify-center overflow-hidden mb-16 shadow-sm"
        style="background-image: url('{{ asset('images/itech.png') }}'); background-attachment: fixed; background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-gradient-to-b from-red-950/80 via-[#1b1b18]/65 to-[#1b1b18]/85"></div>
        <div class="relative z-10 text-center px-6 py-24 max-w-3xl mx-auto">
            <p class="text-[#FFC107] text-xs font-semibold tracking-[0.25em] uppercase mb-3"><span
                    class="text-[#afafaf]">A</span> Capstone Project <span class="text-[#afafaf]">dedicated
                    to</span> Teknolohistas ng Bayan &middot; PUP&ndash;ITECH<span
                    class="text-[#afafaf]">.</span>
            </p>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-5 leading-tight">
                Welcome to <span class="text-red-900    ">Alumni</span><span class="text-[#FFC107]">Hub</span>
            </h1>
            <p class="text-gray-300 text-sm md:text-base mb-10 max-w-xl mx-auto leading-relaxed">
                Connect with fellow Teknolohistas ng Bayan academically and professionally. Together, stay tuned,
                coordinated, and thriving all-in-one platform. Growth never stops, explore your space here at <span
                    class="text-red-900">Alumni</span><span class="text-[#ffc107]">Hub</span>.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="#features"
                    class="inline-flex items-center justify-center gap-2 rounded-md bg-red-900 px-6 py-2.5 text-sm font-semibold tracking-widest text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2 transition ease-in-out duration-150">
                    Learn More
                    <i class="fa-regular fa-lightbulb" style="color: white !important;"></i>
                </a>
                <a href="#alumnihub-team"
                    class="inline-flex items-center justify-center gap-2 rounded-md border border-white/25 bg-white/10 backdrop-blur-sm px-6 py-2.5 text-sm font-semibold tracking-widest text-white hover:bg-white/20 transition ease-in-out duration-150">
                    The Team
                    <i class="fa-solid fa-hand-holding-hand" style="color: white !important;"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- bg__mask -->
    <div class="bg-center bg-no-repeat [background-size:auto] flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0 mx-auto px-4 sm:px-6 lg:px-8 pb-24"
        style="background-image: url('{{ asset('images/element.png') }}');">
        <main class="flex flex-col max-w-7xl w-full">
            <!-- <section id="parallax-hero"
                class="text-[13px] leading-[20px] flex-1 p-6 pb-6 lg:p-20 lg:pb-10 dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] lg:rounded-tl-lg lg:rounded-br-none">
                <h2 class="mb-1 font-medium text-xl md:text-2xl lg:text-3xl relative inline-block">
                    <span class="relative z-10 text-[#4a4a4a]">
                        What is <span class="text-red-900">Alumni</span><span class="text-[#FFC107]">Hub</span>?
                    </span>
                    <img src="{{ asset('images/paint-stroke.png') }}" alt="paint stroke"
                        class="absolute left-0 bottom-0 w-full h-auto z-0 pointer-events-none" />
                </h2>
                <p class="mb-2 text-gray-600 lg:text-base">AlumniHub is a CAPSTONE web-based platform project
                    designed to connect
                    graduates and students of the Polytechnic University of the Philippines – Institute of
                    Technology
                    (PUP ITECH).</p>

                <h2 class="mb-1 font-medium text-xl md:text-2xl lg:text-3xl relative inline-block">
                    <span class="relative z-10 text-[#4a4a4a]">
                        Why <span class="text-red-900">Alumni</span><span class="text-[#FFC107]">Hub</span>?
                    </span>
                    <img src="{{ asset('images/paint-stroke.png') }}" alt="paint stroke"
                        class="absolute left-0 bottom-0 w-full h-auto z-0 pointer-events-none" />
                </h2>
                <p class="mb-2 text-gray-600 lg:text-base">Lorem ipsum dolor sit amet consectetur adipisicing elit.
                    Eveniet at sequi velit expedita tempore alias, cumque adipisci quidem ratione blanditiis vero
                    exercitationem quibusdam laudantium et nulla. Ullam magnam cumque molestias corrupti non?
                    Corporis nihil esse vel corrupti eius pariatur, impedit, incidunt dolore nisi sed, quis iste
                    nobis enim et illum!.</p> -->

            <!-- <a href="/about" target="_blank"
                        class="inline-flex items-center justify-center gap-2 rounded-md bg-red-900 px-3 py-2 text-xs leading-normal mt-8 font-semibold tracking-widest text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2 transition ease-in-out duration-150">
                        Learn More
                        <svg width="18" height="10" viewBox="0 0 20 13" fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M0.000139952 7.36387L0 5.36397H16.1719L12.2222 1.41421L13.6364 0L20.0004 6.36397L13.6364 12.728L12.2222 11.3137L16.172 7.36397L0.000139952 7.36387Z"
                                fillF="currentColor" />
                        </svg>
                    </a> -->

            <!-- itech -->
            <!-- <div
                    class="bg-white relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-tl-none lg:rounded-r-lg aspect-[335/364] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">
                    <img src="{{ asset('images/itech.png') }}" alt="Institute of Technology"
                        class="bg-cover bg-center h-full w-full absolute inset-0 object-cover rounded-2xl rounded-b-none lg:rounded-bl-full filter saturate-90 brightness-100 contrast-[80%]" />
                </div> -->
            </section>

            <section id="features" class="py-16 px-2">
                <div class="text-center mb-12">
                    <h2 class="mb-1 font-medium text-xl md:text-2xl lg:text-3xl relative inline-block">
                        <span class="relative z-10 text-[#4a4a4a]">
                            What <span class="text-red-900">Alumni</span><span class="text-[#FFC107]">Hub</span> Offers
                        </span>
                        <img src="{{ asset('images/paint-stroke.png') }}" alt="paint stroke"
                            class="absolute left-0 bottom-0 w-full h-auto z-0 pointer-events-none" />
                    </h2>
                    <p class="mt-4 text-gray-500 text-sm md:text-base max-w-xl mx-auto">
                        Everything you need to stay hand-in-hand — in one place.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
                    <!-- in-app notif -->
                    <div
                        class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition duration-200">
                        <div class="w-11 h-11 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-900" fill="none" stroke="currentColor" stroke-width="1.75"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-[#1b1b18] mb-2">In-App Notifications</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Get in-app alerts for likes, comments,
                            messages, and invites. Stay timely in track, organized, and ready to engage in action.</p>
                    </div>

                    <!-- messaging -->
                    <div
                        class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition duration-200">
                        <div class="w-11 h-11 bg-yellow-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#b8860b]" fill="none" stroke="currentColor" stroke-width="1.75"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-[#1b1b18] mb-2">Direct Messaging</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Communicate 1-to-1 in real-time with fellow
                            students and alumni through private chat powered by Pusher.</p>
                    </div>

                    <!-- alumni feed -->
                    <div
                        class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition duration-200">
                        <div class="w-11 h-11 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-900" fill="none" stroke="currentColor" stroke-width="1.75"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-[#1b1b18] mb-2">Alumni Feed</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Manage and share your text posts, photos, and
                            updates. Stay informed and engaged with what's relevant in your network.</p>
                    </div>

                    <!-- event announcements -->
                    <div
                        class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition duration-200">
                        <div class="w-11 h-11 bg-yellow-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#b8860b]" fill="none" stroke="currentColor" stroke-width="1.75"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-[#1b1b18] mb-2">Event Announcements</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Create and discover alumni events. Receive
                            auto-invites to ensure you never miss a webinar, reunion, or in person workshops.</p>
                    </div>

                    <!-- alumni directory -->
                    <div
                        class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition duration-200">
                        <div class="w-11 h-11 bg-red-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-900" fill="none" stroke="currentColor" stroke-width="1.75"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-[#1b1b18] mb-2">Alumni Directory</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Browse and search fellow Teknolohistas ng
                            Bayan.
                            Build connections professionally and academically within the PUP–ITECH communities</p>
                    </div>

                    <!-- profile management -->
                    <div
                        class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition duration-200">
                        <div class="w-11 h-11 bg-yellow-50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#b8860b]" fill="none" stroke="currentColor" stroke-width="1.75"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-[#1b1b18] mb-2">Profile Management</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Personalize your profile with your desired
                            avatar, banner, posts, and career highlights to stay recognized.</p>
                    </div>
                </div>
            </section>

            <section id="alumnihub-team" class="py-16 px-2">
                <div class="text-center mb-12">
                    <h2 class="mb-1 font-medium text-xl md:text-2xl lg:text-3xl relative inline-block">
                        <span class="relative z-10 text-[#4a4a4a]">
                            Meet the <span class="text-red-900">Alumni</span><span class="text-[#FFC107]">Hub</span>
                            Team
                        </span>
                        <img src="{{ asset('images/paint-stroke.png') }}" alt="paint stroke"
                            class="absolute left-0 bottom-0 w-full h-auto z-0 pointer-events-none" />
                    </h2>
                    <p class="mt-4 text-gray-500 text-sm md:text-base max-w-xl mx-auto">
                        A dedicated group from PUP – Institute of Technology.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-6 max-w-5xl mx-auto sm:flex sm:flex-wrap sm:justify-center sm:gap-10">
                    <!-- don -->
                    <div class="flex flex-col items-center text-center w-full sm:w-40">
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gray-100 border-2 border-red-900 flex items-center justify-center mb-3 overflow-hidden">
                            <img src="{{ asset('images/donald.jpg') }}" alt="Donald R. Oclarit">
                        </div>
                        <h3 class="font-semibold text-[#1b1b18] text-sm">Donald R. Oclarit</h3>
                        <span
                            class="inline-block mt-1 text-xs font-medium text-white bg-red-900 rounded-full px-2.5 py-0.5">Leader</span>
                    </div>

                    <!-- mariane -->
                    <div class="flex flex-col items-center text-center w-full sm:w-40">
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gray-100 border-2 border-[#ffc107] flex items-center justify-center mb-3 overflow-hidden">
                            <img src="{{ asset('images/mariane.png') }}" alt="Mariane Lorraine J. Montoya"
                                class="w-full h-full object-cover bg-cover bg-center rounded-full">
                        </div>
                        <h3 class="font-semibold text-[#1b1b18] text-sm">Mariane Lorraine J. Montoya</h3>
                        <span
                            class="inline-block mt-1 text-xs font-medium text-amber-800 bg-[#ffc107] rounded-full px-2.5 py-0.5">Asst.
                            Leader</span>
                    </div>

                    <!-- jazper -->
                    <div class="flex flex-col items-center text-center w-full sm:w-40">
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gray-100 border-2 border-emerald-700 flex items-center justify-center mb-3 overflow-hidden">
                            <img src="{{ asset('images/jazper.png') }}" alt="Jazper Sid A. Ga"
                                class="w-full h-full object-cover bg-cover bg-center rounded-full">
                        </div>
                        <h3 class="font-semibold text-[#1b1b18] text-sm">Jazper Sid A. Ga</h3>
                        <span
                            class="inline-block mt-1 text-xs font-medium text-white bg-emerald-700 rounded-full px-2.5 py-0.5">Researcher</span>
                    </div>

                    <!-- lea -->
                    <div class="flex flex-col items-center text-center w-full sm:w-40">
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gray-100 border-2 border-emerald-700 flex items-center justify-center mb-3 overflow-hidden">
                            <img src="{{ asset('images/lea.jpg') }}" alt="Lea Mae D. Leona"
                                class="w-full h-full object-cover bg-cover bg-center rounded-full">
                        </div>
                        <h3 class="font-semibold text-[#1b1b18] text-sm">Lea Mae D. Leona</h3>
                        <span
                            class="inline-block mt-1 text-xs font-medium text-white bg-emerald-700 rounded-full px-2.5 py-0.5">Researcher</span>
                    </div>

                    <!-- me -->
                    <div class="flex flex-col items-center text-center col-span-2 w-full sm:w-40">
                        <div
                            class="dev relative w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gray-100 border-2 border-[#0a0a0a] flex items-center justify-center mb-3 overflow-hidden">
                            <img src="{{ asset('images/sajo.jpg') }}" alt="Yngwie Johanne M. Punsalan">
                        </div>
                        <h3 class="font-semibold text-[#1b1b18] text-sm">Yngwie Johanne M. Punsalan</h3>
                        <span
                            class="relative inline-flex items-center justify-center mt-1 text-xs font-semibold text-white rounded-full px-3 py-1 overflow-hidden h-6">
                            <video
                                class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-90 saturate-200 contrast-150 brightness-110 scale-110"
                                autoplay muted loop playsinline preload="auto" aria-hidden="true" tabindex="-1">
                                <source src="{{ asset('images/flame.mp4') }}" type="video/mp4">
                            </video>
                            <span class="pointer-events-none absolute inset-0 bg-black/35"></span>
                            <span class="relative z-10 drop-shadow-sm">Developer</span>
                        </span>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <x-footer />
    @if (Route::has('login'))
        <div class="h-14.5 hidden lg:block"></div>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const header = document.getElementById('main-header');
            if (!header) return;

            const brandText = header.querySelector('.nav-text-brand');
            const navButtons = header.querySelectorAll('.nav-btn-outline');

            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    header.classList.remove('bg-transparent', 'text-white');
                    header.classList.add('bg-gradient-to-r', 'from-white', 'via-amber-50', 'to-red-900/10', 'backdrop-blur-md', 'shadow-sm', 'text-[#1b1b18]');

                    if (brandText) {
                        brandText.classList.remove('text-white');
                        brandText.classList.add('text-red-900');
                        brandText.classList.remove('border-white');
                        brandText.classList.add('border-red-900');
                    }

                    navButtons.forEach(btn => {
                        btn.classList.remove('text-white', 'border-white');
                        btn.classList.add('text-red-900', 'border-red-900');
                    });

                } else {
                    // rev
                    header.classList.remove('bg-gradient-to-r', 'from-white', 'via-amber-50', 'to-red-900/10', 'backdrop-blur-md', 'shadow-sm', 'text-[#1b1b18]');
                    header.classList.add('bg-transparent', 'text-white');

                    if (brandText) {
                        brandText.classList.remove('text-red-900');
                        brandText.classList.add('text-white');
                    }

                    navButtons.forEach(btn => {
                        btn.classList.remove('text-red-900', 'border-red-900');
                        btn.classList.add('text-white', 'border-white');
                    });
                }
            });
        });
    </script>
</body>

</html>