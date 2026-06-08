@php
    /** @var \App\Models\User|null $navUser */
    $navUser = Auth::user();
    $unreadNotificationsCount = $navUser?->notifications()->whereNull('read_at')->whereNull('trashed_at')->count() ?? 0;
    $unreadMessagesCount = $navUser?->unreadMessagesCount() ?? 0;
    $adminPendingCount = ($navUser?->role === 'admin')
        ? \App\Models\CommunityCreationRequest::where('status', 'pending_admin')->count()
            + \App\Models\VerificationDocument::where('status', 'pending')->count()
        : 0;
@endphp
<nav x-data="{ open: false, notifCount: {{ $unreadNotificationsCount }}, msgCount: {{ $unreadMessagesCount }}, adminPendingCount: {{ $adminPendingCount }} }"
    x-init="
        setInterval(async () => { try { const r = await fetch('{{ route('notifications.unread-count') }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }); if (!r.ok) return; const d = await r.json(); notifCount = d.count; } catch {} }, 30000);
        @if ($navUser?->role !== 'admin')
        const refreshMsgCount = async () => { try { const r = await fetch('{{ route('messages.unread-count') }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }); if (!r.ok) return; const d = await r.json(); msgCount = d.count; } catch {} };
        setInterval(refreshMsgCount, 30000);
        window.addEventListener('message-count-refresh', refreshMsgCount);
        if (window.Echo) { window.Echo.private('user.{{ $navUser?->id }}').listen('.MessageSent', () => refreshMsgCount()); }
        @endif
        @if ($navUser?->role === 'admin')
        setInterval(async () => { try { const r = await fetch('{{ route('admin.inbox.counts') }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }); if (!r.ok) return; const d = await r.json(); adminPendingCount = d.total; } catch {} }, 30000);
        @endif
    "
    class="sticky top-0 z-40 bg-gradient-to-r from-white via-amber-50 to-red-900/10 backdrop-blur-md shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between gap-4">
            <div class="flex min-w-0">
                <!-- Logo -->
                <a href="{{ url('/') }}"
                    class="flex shrink-0 items-center gap-2 sm:gap-4 cursor-pointer decoration-none">
                    <img src="{{ asset('images/alumnihub-logo.png') }}" alt="AlumniHub Logo" class="w-6 sm:w-8" />
                    <h1 class="font-bold text-base sm:text-xl ">
                        <span class="text-red-900">Alumni</span><span class="text-[#FFC107]">Hub</span>
                    </h1>
                </a>

                <!-- Navigation Links -->
                <div class="hidden lg:-my-px lg:ms-10 lg:flex lg:space-x-8">
                    @if (Auth::user()?->role === 'admin')
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            <i class="fa-regular fa-house text-lg"></i>
                            {{ __('Admin Home') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.communities.index')"
                            :active="request()->routeIs('admin.communities.*')">
                            <i class="fa-solid fa-group-arrows-rotate text-lg"></i>
                            {{ __('Communities') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.verifications.index')"
                            :active="request()->routeIs('admin.verifications.*')">
                            <i class="fa-solid fa-user-check text-lg"></i>
                            {{ __('Verification Queue') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.reports.index')"
                            :active="request()->routeIs('admin.reports.*')">
                            <i class="fa-solid fa-flag text-lg"></i>
                            {{ __('Reported Posts') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.inbox')" :active="request()->routeIs('admin.inbox*')">
                            <span class="relative">
                                <i class="fa-regular fa-bell text-lg"></i>
                                <span x-show="adminPendingCount > 0" x-text="adminPendingCount" x-cloak
                                    class="absolute -top-0.5 -end-2 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-100 px-1 text-[10px] font-medium text-amber-800 ring-1 ring-inset ring-amber-200"></span>
                            </span>
                            {{ __('Inbox') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            <i class="fa-regular fa-house text-lg"></i>
                            {{ __('Home') }}
                        </x-nav-link>

                        <x-nav-link :href="route('connections.index')"
                            :active="request()->routeIs('connections.*')">
                            <i class="fa-solid fa-users text-lg"></i>
                            {{ __('Connections') }}
                        </x-nav-link>

                        <x-nav-link :href="route('communities.index')" :active="request()->routeIs('communities.*')">
                            <i class="fa-solid fa-group-arrows-rotate text-lg"></i>
                            {{ __('Communities') }}
                        </x-nav-link>

                        <x-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">
                            <span class="relative">
                                <i class="fa-regular fa-message text-lg"></i>
                                <span x-show="msgCount > 0" x-text="msgCount" x-cloak
                                    class="absolute -top-0.5 -end-2 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-100 px-1 text-[10px] font-medium text-amber-800 ring-1 ring-inset ring-amber-200"></span>
                            </span>
                            {{ __('Messages') }}
                        </x-nav-link>

                        <x-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                            <span class="relative">
                                <i class="fa-regular fa-bell text-lg"></i>
                                <span x-show="notifCount > 0" x-text="notifCount" x-cloak
                                    class="absolute -top-0.5 -end-2 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-100 px-1 text-[10px] font-medium text-amber-800 ring-1 ring-inset ring-amber-200"></span>
                            </span>
                            {{ __('Notifications') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden shrink-0 lg:ms-6 lg:flex lg:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="flex flex-col items-center text-sm font-medium text-red-900 hover:text-[#FFC107] focus:outline-none transition ease-in-out duration-150">
                            <img src="{{ Auth::user()->profileAvatarUrl() }}"
                                alt="{{ Auth::user()->name }}"
                                class="h-8 w-8 shrink-0 rounded-full border border-red-900/20 object-cover"
                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                            <div class="inline-flex items-center">
                                <span>{{ __('Me') }}</span>
                                <svg class="fill-current h-4 w-4 transition-transform duration-200"
                                    :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profiles.show', Auth::id())">
                            <i class="fa-solid fa-circle-user"></i>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if (session()->has(\App\Http\Controllers\InstitutionSwitchController::SESSION_KEY) && $navUser?->isInstitution())
                            {{-- Acting as PUP-ITECH Official: offer a way back to the admin --}}
                            <form method="POST" action="{{ route('institution.exit') }}">
                                @csrf
                                <x-dropdown-link :href="route('institution.exit')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                    {{ __('Exit Superadmin') }}
                                </x-dropdown-link>
                            </form>
                        @elseif ($navUser?->role === 'admin')
                            <button type="button" @click="$dispatch('open-institution-confirm')"
                                class="inline-flex items-center gap-[6px] md:gap-2 w-full px-4 py-2 text-start text-sm leading-5 text-gray-600 hover:bg-red-900 hover:text-white focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                <i class="fa-solid fa-user-shield"></i>
                                {{ __('Enable Superadmin') }}
                            </button>
                        @else
                            <x-dropdown-link :href="route('profile.edit', ['section' => 'account-status'])">
                                <i class="fa-solid fa-pen-to-square"></i>
                                {{ __('Edit Profile Settings') }}
                            </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center lg:hidden">
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
    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if (Auth::user()?->role === 'admin')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <i class="fa-solid fa-house"></i>
                    {{ __('Admin Home') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.communities.index')"
                    :active="request()->routeIs('admin.communities.*')">
                    <i class="fa-solid fa-group-arrows-rotate"></i>
                    {{ __('Communities') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.verifications.index')"
                    :active="request()->routeIs('admin.verifications.*')">
                    <i class="fa-solid fa-user-check"></i>
                    {{ __('Verification Queue') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.reports.index')"
                    :active="request()->routeIs('admin.reports.*')">
                    <i class="fa-solid fa-flag"></i>
                    {{ __('Reported Posts') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.inbox')" :active="request()->routeIs('admin.inbox*')">
                    <i class="fa-regular fa-bell"></i>
                    {{ __('Inbox') }}
                    <span x-show="adminPendingCount > 0" x-text="adminPendingCount"
                        class="ms-2 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-200"></span>
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <i class="fa-regular fa-house"></i>
                    {{ __('Home') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('connections.index')"
                    :active="request()->routeIs('connections.*')">
                    <i class="fa-solid fa-users"></i>
                    {{ __('Connections') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('communities.index')" :active="request()->routeIs('communities.*')">
                    <i class="fa-solid fa-group-arrows-rotate"></i>
                    {{ __('Communities') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">
                    <i class="fa-regular fa-message"></i>
                    {{ __('Messages') }}
                    <span x-show="msgCount > 0" x-text="msgCount"
                        class="ms-2 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-200"></span>
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                    <i class="fa-regular fa-bell"></i>
                    {{ __('Notifications') }}
                    <span x-show="notifCount > 0" x-text="notifCount"
                        class="ms-2 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-200"></span>
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-[#afafaf]">
            <div class="px-4">
                <div class="font-medium text-lg text-red-900">{{ Auth::user()->name }}</div>
                <div class="font-medium text-xs text-[#FFC107]">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profiles.show', Auth::id())">
                    <i class="fa-solid fa-circle-user"></i>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (session()->has(\App\Http\Controllers\InstitutionSwitchController::SESSION_KEY) && $navUser?->isInstitution())
                    <form method="POST" action="{{ route('institution.exit') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('institution.exit')" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="fa-solid fa-arrow-rotate-left"></i>
                            {{ __('Exit Superadmin') }}
                        </x-responsive-nav-link>
                    </form>
                @elseif ($navUser?->role === 'admin')
                    <button type="button" @click="open = false; $dispatch('open-institution-confirm')"
                        class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-red-900 hover:bg-gray-50 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out">
                        <i class="fa-solid fa-user-shield"></i>
                        {{ __('Enable Superadmin') }}
                    </button>
                @else
                    <x-responsive-nav-link :href="route('profile.edit')">
                        <i class="fa-solid fa-pen-to-square"></i>
                        {{ __('Edit Profile Settings') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>

</nav>

{{-- Confirmation modal: admin switching into the PUP-ITECH Official account.
     Lives OUTSIDE <nav> with its own Alpine scope so `fixed` is viewport-relative
     (the nav is a positioned/stacking context). Opened via a window event. --}}
@if ($navUser?->role === 'admin' && ! session()->has(\App\Http\Controllers\InstitutionSwitchController::SESSION_KEY))
    <div x-data="{ show: false }" x-show="show" x-cloak
        @open-institution-confirm.window="show = true"
        @keydown.escape.window="show = false"
        class="fixed inset-0 z-[600] flex items-center justify-center p-4" style="display:none">
        <div class="fixed inset-0 bg-black/50" @click="show = false"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-700">
                    <i class="fa-solid fa-user-shield"></i>
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">{{ __('Switch to PUP-ITECH Official?') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('You will act as the institution account — browsing, posting, connecting and messaging as PUP-ITECH Official. You can return to the admin panel anytime from the menu.') }}
                    </p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="show = false"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    {{ __('Cancel') }}
                </button>
                <form method="POST" action="{{ route('admin.institution.enter') }}">
                    @csrf
                    <button type="submit"
                        class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600">
                        {{ __('Confirm switch') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Persistent banner while acting as the institution account — fixed to the
     bottom so it never overlaps the nav on scroll. --}}
@if (session()->has(\App\Http\Controllers\InstitutionSwitchController::SESSION_KEY) && $navUser?->isInstitution())
    <div class="fixed inset-x-0 bottom-0 z-[60] flex items-center justify-center gap-3 bg-blue-700 px-4 py-2 text-center text-xs font-medium text-white shadow-[0_-2px_8px_rgba(0,0,0,0.15)]">
        <span>
            <i class="fa-solid fa-user-shield me-1"></i>
            {{ __('You are acting as PUP-ITECH Official') }}
        </span>
        <form method="POST" action="{{ route('institution.exit') }}">
            @csrf
            <button type="submit" class="rounded-full bg-white/20 px-2.5 py-0.5 font-semibold hover:bg-white/30">
                {{ __('Return to admin') }}
            </button>
        </form>
    </div>
@endif