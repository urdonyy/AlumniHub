<nav x-data="{ open: false }" class="bg-red-900/10">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center gap-2 sm:gap-4 cursor-pointer decoration-none">
                    <img src="{{ asset('images/alumnihub-logo.png') }}" alt="AlumniHub Logo" class="w-6 sm:w-8" />
                    <h1 class="font-bold text-base sm:text-xl ">
                        <span class="text-red-900">Alumni</span><span class="text-[#FFC107]">Hub</span>
                    </h1>
                </a>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if (Auth::user()?->role === 'admin')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Admin Home') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.communities.index')"
                            :active="request()->routeIs('admin.communities.*')">
                            {{ __('Communities') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.verifications.index')"
                            :active="request()->routeIs('admin.verifications.*')">
                            {{ __('Verification Queue') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Home') }}
                        </x-nav-link>

                        <x-nav-link :href="route('profiles.show', Auth::id())"
                            :active="request()->routeIs('profiles.show')">
                            {{ __('Profile') }}
                        </x-nav-link>

                        <x-nav-link :href="route('communities.index')" :active="request()->routeIs('communities.*')">
                            {{ __('Communities') }}
                        </x-nav-link>

                        <x-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">
                            {{ __('Messages') }}
                        </x-nav-link>

                        <x-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                            {{ __('Notifications') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-red-900 text-sm leading-4 font-medium rounded-md text-red-900 bg-white hover:text-[#FFC107] hover:border-[#FFC107] focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-red-900 hover:text-[#FFC107] focus:outline-none focus:border-red-900 focus:text-[#FFC107] transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if (Auth::user()?->role === 'admin')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Admin Home') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.communities.index')"
                    :active="request()->routeIs('admin.communities.*')">
                    {{ __('Communities') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.verifications.index')"
                    :active="request()->routeIs('admin.verifications.*')">
                    {{ __('Verification Queue') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Home') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('profiles.show', Auth::id())"
                    :active="request()->routeIs('profiles.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('communities.index')" :active="request()->routeIs('communities.*')">
                    {{ __('Communities') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">
                    {{ __('Messages') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                    {{ __('Notifications') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-[#afafaf]">
            <div class="px-4">
                <div class="font-medium text-base text-red-900">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-[#FFC107]">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>