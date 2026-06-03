<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                {{ auth()->user()->canManageCommunities() ? __('Admin Home') : __('Home') }}
            </h2>
            <p class="text-sm text-red-900">{{ __('AlumniHub social experience (beta)') }}</p>
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">
            @if (session('status') === 'registration-complete')
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Registration submitted — pending admin review</p>
                        <p class="mt-0.5 text-sm text-amber-700">Your verification document has been received. You'll gain full access once an admin approves your account.</p>
                    </div>
                </div>
            @endif

            @if (auth()->user()->canManageCommunities())
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-gray-500">{{ __('Community Access') }}</p>
                        <div
                            class="mt-3 inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ auth()->user()->communityAccessBadgeClass() }}">
                            {{ auth()->user()->communityAccessLabel() }}
                        </div>
                        <p class="mt-4 text-sm text-gray-600">
                            {{ __('Use this admin homepage to review verifications and manage community assignment rules.') }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-indigo-900">{{ __('Admin Shortcuts') }}</h3>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ route('admin.communities.index') }}"
                                class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-600">
                                {{ __('Manage Communities') }}
                            </a>
                            <a href="{{ route('admin.verifications.index') }}"
                                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
                                {{ __('Review Verifications') }}
                            </a>
                        </div>
                    </div>
                </div>
            @else
                @php
                    $feedCards = [
                        [
                            'author' => 'Career Services Team',
                            'meta' => 'Campus Update',
                            'content' => 'Sample post placeholder: internship matching and alumni mentorship updates will appear here once posting is enabled.',
                        ],
                        [
                            'author' => 'Community Spotlight',
                            'meta' => 'Batch Highlight',
                            'content' => 'Sample post placeholder: your batch highlights and community stories will show in this section.',
                        ],
                        [
                            'author' => 'AlumniHub',
                            'meta' => 'Product Note',
                            'content' => 'Sample post placeholder: reactions, comments, and sharing are part of the next backend phase.',
                        ],
                    ];
                @endphp

                <div class="grid gap-6 md:grid-cols-3 lg:grid-cols-12">
                    <aside class="space-y-3 min-w-0 md:hidden lg:block lg:col-span-3 lg:sticky lg:top-6 lg:self-start">
                        <a href="{{ route('profiles.show', auth()->id()) }}"
                            class="block rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-gray-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Profile') }}</p>
                            <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</h3>
                            <p class="text-sm text-gray-600">{{ auth()->user()->program_course ?? __('Program pending') }}</p>
                            <div
                                class="mt-3 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ auth()->user()->accountStatusBadgeClass() }}">
                                {{ auth()->user()->accountStatusLabel() }}
                            </div>
                        </a>

                        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Shortcuts') }}</p>
                            <div class="mt-3 space-y-2 text-sm">
                                <a class="block text-gray-700 hover:text-gray-900" href="{{ route('communities.index') }}">{{ __('My Communities') }}</a>
                                <a class="block text-gray-700 hover:text-gray-900" href="{{ route('connections.index') }}">{{ __('My Connections') }}</a>
                                <a class="block text-gray-700 hover:text-gray-900" href="{{ route('profile.edit', ['section' => 'account-status']) }}">{{ __('Account Settings') }}</a>
                            </div>
                        </section>
                    </aside>

                    <section class="space-y-3 min-w-0 md:col-span-2 lg:col-span-6" x-data="feedManager()" @feedManager-openPostModal.window="openPostModal($event, $event.detail.postId, $event.detail.apiUrl, $event.detail.commentsUrl)">
                        @php
                            /** @var \Illuminate\Support\Collection<int, \App\Models\Community> $joinedCommunitiesCollection */
                            $joinedCommunitiesCollection = collect($joinedCommunities ?? []);
                            $composerGeneralHubId = $joinedCommunitiesCollection->firstWhere('system_key', 'general-alumni-hub')->id ?? null;
                            $defaultCommunityId = old('community_id') ?? $composerGeneralHubId;
                        @endphp

                        @if (! auth()->user()->isVerified())
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-amber-900">{{ __('Verify your account to start posting') }}</h3>
                                        <p class="mt-1 text-sm text-amber-800">
                                            {{ __('You\'re browsing AlumniHub in read-only mode. Verify your alumni status to share posts, like, comment, and connect with your batchmates.') }}
                                        </p>
                                        @if (auth()->user()->hasPendingVerificationDocument())
                                            <div class="relative group mt-3 inline-block">
                                                <span class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg bg-amber-200 px-4 py-2 text-sm font-semibold text-amber-500">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ __('Pending review') }}
                                                </span>
                                                <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 px-2.5 py-1.5 text-xs text-white group-hover:block">
                                                    {{ __('Waiting for admin approval') }}
                                                </div>
                                            </div>
                                        @else
                                            <a href="{{ route('profile.edit', ['section' => 'verification-document']) }}"
                                                class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ __('Verify now') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                        @include('partials.post-composer')
                        @endif

                        @include('partials.feed-region')

                        <x-post-detail-modal />
                    </section>

                    <aside class="space-y-3 min-w-0 md:col-span-1 lg:col-span-3 md:sticky md:top-6 md:self-start">
                        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Batch Communities') }}</h3>
                                @if ($featuredCommunities->count() > 3)
                                    <a href="{{ route('communities.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-700">
                                        {{ __('See all') }}
                                    </a>
                                @endif
                            </div>

                            <div class="mt-4 space-y-3">
                                @forelse ($featuredCommunities->take(3) as $community)
                                    <a href="{{ route('communities.show', $community) }}"
                                        class="block rounded-lg border border-gray-200 px-3 py-2 transition hover:border-gray-300 hover:bg-gray-50">
                                        <p class="text-sm font-semibold text-gray-900">{{ $community->name }}</p>
                                        <p class="text-xs text-gray-600">
                                            {{ trans_choice('{1} :count member|[2,*] :count members', $community->members_count, ['count' => $community->members_count]) }}
                                        </p>
                                    </a>
                                @empty
                                    <p class="rounded-lg border border-dashed border-gray-300 px-3 py-3 text-xs text-gray-500">
                                        {{ __('Community highlights will appear here once more communities are available.') }}
                                    </p>
                                @endforelse
                            </div>
                        </section>

                        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Suggested People') }}</h3>
                                @if ($suggestedPeople->count() > 3)
                                    <a href="{{ route('connections.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-700">
                                        {{ __('See all') }}
                                    </a>
                                @endif
                            </div>
                            @php $isVerified = auth()->user()->isVerified(); @endphp
                            <div class="relative mt-4">
                                <div class="space-y-3 {{ $isVerified ? '' : 'pointer-events-none select-none blur-sm' }}"
                                    @unless($isVerified) aria-hidden="true" @endunless>
                                    @forelse ($suggestedPeople->take(3) as $person)
                                        <a href="{{ $isVerified ? route('profiles.show', $person) : '#' }}"
                                            class="block rounded-lg border border-gray-200 px-3 py-2 transition hover:border-gray-300 hover:bg-gray-50">
                                            <p class="text-sm font-semibold text-gray-900">{{ $person->name }}</p>
                                            <p class="text-xs text-gray-600">{{ $person->program_course ?? __('Program pending') }}</p>
                                        </a>
                                    @empty
                                        <p class="rounded-lg border border-dashed border-gray-300 px-3 py-3 text-xs text-gray-500">
                                            {{ __('People suggestions will be populated after connection features are enabled.') }}
                                        </p>
                                    @endforelse
                                </div>

                                @unless ($isVerified)
                                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 rounded-lg bg-white/40 px-4 text-center">
                                        <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <p class="text-xs font-semibold text-gray-700">{{ __('Verify your account to see people you may know.') }}</p>
                                        @if (auth()->user()->hasPendingVerificationDocument())
                                            <div class="relative group inline-block">
                                                <span class="cursor-not-allowed text-xs font-semibold text-amber-400">{{ __('Pending review') }}</span>
                                                <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 px-2.5 py-1.5 text-xs text-white group-hover:block">
                                                    {{ __('Waiting for admin approval') }}
                                                </div>
                                            </div>
                                        @else
                                            <a href="{{ route('profile.edit', ['section' => 'verification-document']) }}"
                                                class="text-xs font-semibold text-amber-700 underline hover:text-amber-800">{{ __('Verify now') }}</a>
                                        @endif
                                    </div>
                                @endunless
                            </div>
                        </section>
                    </aside>
                </div>
            @endif
        </div>
    </div>

    @include('partials.feed-scripts')

    @if (session()->has('openPostModal'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const detail = @json(session('openPostModal'));
                if (!detail) return;
                window.dispatchEvent(new CustomEvent('post-modal-opened', { detail }));
            });
        </script>
    @endif

    {{-- "Let's get you started" verification prompt (shown to unverified, non-pending users after registration / login) --}}
    @if (session('show_setup_prompt') && ! auth()->user()->isVerified() && ! auth()->user()->hasPendingVerificationDocument())
        <div x-data="{ open: true }"
            x-show="open"
            x-transition.opacity
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-[600] flex items-center justify-center bg-black/60 p-4"
            style="display: none;">
            <div @click.away="open = false"
                class="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">
                <div class="flex flex-col items-center gap-3 px-6 pt-8 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">{{ __("Let's get you started") }}</h3>
                    <p class="text-sm leading-relaxed text-gray-600">
                        {{ __('Welcome to AlumniHub! Your account is currently unverified, so you can only browse public posts. Kindly wait for your status to be verified to unlock the full experience, post, like, comment, join community discussions, and connect with your batchmates.') }}
                    </p>
                </div>
                <div class="flex gap-3 px-6 py-6">
                    <button type="button" @click="open = false"
                        class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        {{ __('Skip for now') }}
                    </button>
                    @if (auth()->user()->hasPendingVerificationDocument())
                        <div class="relative group flex-1">
                            <span class="block w-full cursor-not-allowed rounded-lg bg-amber-200 px-4 py-2.5 text-center text-sm font-semibold text-amber-500">
                                {{ __('Pending review') }}
                            </span>
                            <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 px-2.5 py-1.5 text-xs text-white group-hover:block">
                                {{ __('Waiting for admin approval') }}
                            </div>
                        </div>
                    @else
                        <a href="{{ route('profile.edit', ['section' => 'verification-document']) }}"
                            class="flex-1 rounded-lg bg-amber-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-amber-700">
                            {{ __('Verify') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- "Complete your profile" prompt (shown to users with a pending verification review) --}}
    @if (session('show_setup_prompt') && auth()->user()->hasPendingVerificationDocument())
        <div x-data="{ open: true }"
            x-show="open"
            x-transition.opacity
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-[600] flex items-center justify-center bg-black/60 p-4"
            style="display: none;">
            <div @click.away="open = false"
                class="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">
                <div class="flex flex-col items-center gap-3 px-6 pt-8 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class=”text-lg font-bold text-gray-900”>{{ __('While you wait...') }}</h3>
                    <p class=”text-sm leading-relaxed text-gray-600”>
                        {{ __('Your verification document is under review. In the meantime, complete your profile - add your skills, experience, and education so you are ready to connect with your batchmates the moment you get approved.') }}
                    </p>
                </div>
                <div class="flex gap-3 px-6 py-6">
                    <button type="button" @click="open = false"
                        class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        {{ __('Skip for now') }}
                    </button>
                    <a href="{{ route('profile.edit', ['section' => 'profile-information']) }}"
                        class="flex-1 rounded-lg bg-red-900 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-red-800">
                        {{ __('Set up profile') }}
                    </a>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>

<x-footer />
