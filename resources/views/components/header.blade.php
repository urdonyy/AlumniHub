<header id="main-header"
    class="w-full h-16 text-sm flex items-center justify-between not-has-[nav]:hidden px-2 sm:px-5 fixed top-0 left-0 z-40 transition-all duration-300 {{ request()->is('/') ? 'bg-transparent text-white' : 'bg-gradient-to-r from-white via-amber-50 to-red-900/10 backdrop-blur-md shadow-sm text-[#1b1b18]' }}">
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
                    class="nav-btn-outline font-semibold inline-block px-3 py-1 sm:px-5 sm:py-1.5 {{ request()->is('/') ? 'text-white border-white' : 'text-red-900 border-red-900' }} border hover:border-[#FFC107] hover:text-[#FFC107] rounded-md text-xs sm:text-sm leading-normal whitespace-nowrap">
                    Dashboard
                </a>
            @else
                @if (Route::is('login'))
                    <a href="{{ route('register') }}"
                        class="nav-btn-outline font-semibold inline-block px-3 py-1 sm:px-5 sm:py-1.5 {{ request()->is('/') ? 'text-white border-white' : 'text-red-900 border-red-900' }} border hover:border-[#FFC107] hover:text-[#FFC107] rounded-md text-xs sm:text-sm leading-normal whitespace-nowrap">
                        Register
                    </a>
                @elseif (Route::is('register'))
                    <a href="{{ route('login') }}"
                        class="nav-btn-outline font-semibold inline-block px-3 py-1 sm:px-5 sm:py-1.5 {{ request()->is('/') ? 'text-white border-white' : 'text-red-900 border-red-900' }} border hover:border-[#FFC107] hover:text-[#FFC107] rounded-md text-xs sm:text-sm leading-normal whitespace-nowrap">
                        Log in
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="nav-btn-outline font-semibold inline-block px-3 py-1 sm:px-5 sm:py-1.5 {{ request()->is('/') ? 'text-white border-white' : 'text-red-900 border-red-900' }} border hover:border-[#FFC107] hover:text-[#FFC107] rounded-md text-xs sm:text-sm leading-normal whitespace-nowrap">
                        Log in
                    </a>

                    <a href="{{ route('register') }}"
                        class="nav-btn-outline font-semibold inline-block px-3 py-1 sm:px-5 sm:py-1.5 {{ request()->is('/') ? 'text-white border-white' : 'text-red-900 border-red-900' }} border hover:border-[#FFC107] hover:text-[#FFC107] rounded-md text-xs sm:text-sm leading-normal whitespace-nowrap">
                        Register
                    </a>
                @endif
            @endauth
        </nav>
    @endif
</header>