<header class="bg-red-900/10 w-full text-sm flex items-center justify-between not-has-[nav]:hidden px-2 py-2 sm:px-5">
    <a href="{{ url('/') }}" class="flex items-center gap-2 sm:gap-4 cursor-pointer decoration-none">
        <img src="{{ asset('images/alumnihub-logo.png') }}" alt="AlumniHub Logo" class="w-6 sm:w-8" />
        <h1 class="font-bold text-base sm:text-xl ">
            <span class="text-red-900">Alumni</span><span class="text-[#FFC107]">Hub</span>
        </h1>
    </a>
    @if (Route::has('login'))
        <nav class="flex items-center justify-end gap-2 sm:gap-4">
            @auth
                <a href="{{ url('/dashboard') }}"
                    class="font-semibold inline-block px-3 py-1 sm:px-5 sm:py-1.5 text-red-900 border border-red-900 hover:border-[#FFC107] hover:text-[#FFC107] rounded-sm text-xs sm:text-sm leading-normal whitespace-nowrap">
                    Dashboard
                </a>
            @else
                @if (Route::is('login'))
                    <a href="{{ route('register') }}"
                        class="font-semibold inline-block px-3 py-1 sm:px-5 sm:py-1.5 text-red-900 border border-red-900 hover:border-[#FFC107] hover:text-[#FFC107] rounded-sm text-xs sm:text-sm leading-normal whitespace-nowrap">
                        Register
                    </a>
                @elseif (Route::is('register'))
                    <a href="{{ route('login') }}"
                        class="font-semibold inline-block px-3 py-1 sm:px-5 sm:py-1.5 text-red-900 border border-red-900 hover:border-[#FFC107] hover:text-[#FFC107] rounded-sm text-xs sm:text-sm leading-normal whitespace-nowrap">
                        Log in
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="font-semibold inline-block px-3 py-1 sm:px-5 sm:py-1.5 text-red-900 border border-red-900 hover:border-[#FFC107] hover:text-[#FFC107] rounded-sm text-xs sm:text-sm leading-normal whitespace-nowrap">
                        Log in
                    </a>

                    <a href="{{ route('register') }}"
                        class="font-semibold inline-block px-3 py-1 sm:px-5 sm:py-1.5 text-red-900 border border-red-900 hover:border-[#FFC107] hover:text-[#FFC107] rounded-sm text-xs sm:text-sm leading-normal whitespace-nowrap">
                        Register
                    </a>
                @endif
            @endauth
        </nav>
    @endif
</header>