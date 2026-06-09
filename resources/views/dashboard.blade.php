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
                            <p class="text-sm text-gray-600">{{ auth()->user()->isInstitution() ? __('Official account') : (auth()->user()->program_course ?? __('Program pending')) }}</p>
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
                        <x-report-post-modal />
                        <x-remove-post-modal />
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
                                            <p class="text-xs text-gray-600">{{ $person->isInstitution() ? __('Official account') : ($person->program_course ?? __('Program pending')) }}</p>
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
    @if (session('show_setup_prompt') && auth()->user()->hasPendingVerificationDocument() && ! auth()->user()->profile_setup_completed_at)
    @php
        $wizardAvatarUrl = auth()->user()->profileAvatarUrl();
        $wizardBannerUrl = auth()->user()->profileBannerUrl();
    @endphp
    <div x-data="profileSetupWizard()"
        x-show="open"
        x-transition.opacity
        @keydown.escape.window="close()"
        class="fixed inset-0 z-[600] flex items-center justify-center bg-black/60 p-4"
        style="display: none;">
        <div class="flex w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl"
            style="max-height: 90vh;">

            {{-- Progress bar + step label --}}
            <div class="shrink-0 px-6 pt-6 pb-4 border-b border-gray-100">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex flex-1 gap-1.5">
                        <template x-for="i in [1,2,3,4,5]" :key="i">
                            <div class="h-1 flex-1 rounded-full transition-colors duration-300"
                                :class="i <= step ? 'bg-red-900' : 'bg-gray-200'"></div>
                        </template>
                    </div>
                    <button type="button" x-show="step === 1" @click="close()" aria-label="Close"
                        class="-mt-2.5 shrink-0 rounded-md p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">
                    Step <span x-text="step"></span> of 5 &mdash;
                    <span x-text="['Profile photo', 'Cover photo', 'Experience', 'Education', 'Skills'][step - 1]"></span>
                </p>
            </div>

            {{-- Scrollable step content --}}
            <div class="flex-1 overflow-y-auto px-6 py-5">

                {{-- Step 1: Avatar --}}
                <div x-show="step === 1" class="flex flex-col items-center gap-4 text-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Add a profile photo</h3>
                        <p class="mt-1 text-sm text-gray-500">Help your batchmates recognize you.</p>
                    </div>
                    <div class="relative">
                        <img :src="avatarPreview" alt="Avatar preview"
                            class="h-28 w-28 rounded-full border-2 border-gray-200 bg-gray-100 object-cover" />
                        <label for="wizard-avatar"
                            class="absolute bottom-0 right-0 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-red-900 text-white shadow hover:bg-red-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </label>
                        <input id="wizard-avatar" type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                            @change="onAvatarPick($event)" />
                    </div>
                    <p x-show="avatarFile" class="text-xs font-medium text-green-600">Photo ready &mdash; looking good!</p>
                    <p class="text-xs text-gray-400">Tap the camera icon to upload, then drag &amp; zoom to frame it.</p>
                </div>

                {{-- Step 2: Banner --}}
                <div x-show="step === 2" class="flex flex-col gap-4">
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-900">Add a cover photo</h3>
                        <p class="mt-1 text-sm text-gray-500">A wide banner that appears at the top of your profile.</p>
                    </div>
                    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                        <img :src="bannerPreview" alt="Banner preview"
                            class="h-36 w-full object-cover" />
                        <label for="wizard-banner"
                            class="absolute bottom-2 right-2 flex cursor-pointer items-center gap-1.5 rounded-lg bg-red-900 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-red-800">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Upload
                        </label>
                        <input id="wizard-banner" type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                            @change="onBannerPick($event)" />
                    </div>
                    <p x-show="bannerFile" x-text="bannerFile ? bannerFile.name : ''"
                        class="text-xs font-medium text-green-600"></p>
                </div>

                {{-- Step 3: Experience --}}
                <div x-show="step === 3" class="space-y-3">
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-900">Add your experience</h3>
                        <p class="mt-1 text-sm text-gray-500">Let batchmates know where you have worked.</p>
                    </div>
                    <div class="space-y-3">
                        <template x-for="(exp, i) in experiences" :key="i">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-400"
                                        x-text="'Entry ' + (i + 1)"></span>
                                    <button type="button" @click="removeExperience(i)"
                                        x-show="experiences.length > 1"
                                        class="text-xs font-medium text-rose-600 hover:underline">Remove</button>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <input x-model="exp.title" type="text" placeholder="Role / Title"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-900 focus:ring-red-900" />
                                    <input x-model="exp.organization" type="text" placeholder="Organization"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-900 focus:ring-red-900" />
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="mb-1 block text-xs text-gray-500">Start month</label>
                                        <input x-model="exp.start_month" type="month" :max="todayMonth"
                                            :class="exp.start_month && exp.start_month > todayMonth ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-gray-300 focus:border-red-900 focus:ring-red-900'"
                                            class="block w-full rounded-lg text-sm shadow-sm" />
                                    </div>
                                    <div>
                                        <div class="mb-1 flex items-center justify-between">
                                            <label class="block text-xs text-gray-500">End month</label>
                                            <label class="flex items-center gap-1 text-xs font-medium text-gray-500">
                                                <input type="checkbox" x-model="exp.present" @change="exp.present && (exp.end_month = '')"
                                                    class="h-3.5 w-3.5 rounded border-gray-300 text-red-900 focus:ring-red-900" />
                                                Present
                                            </label>
                                        </div>
                                        <input x-show="!exp.present" x-model="exp.end_month" type="month" :max="todayMonth"
                                            :class="exp.end_month && ((exp.start_month && exp.end_month < exp.start_month) || exp.end_month > todayMonth) ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-gray-300 focus:border-red-900 focus:ring-red-900'"
                                            class="block w-full rounded-lg text-sm shadow-sm" />
                                        <div x-show="exp.present"
                                            class="flex items-center rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm font-medium text-gray-500">Present</div>
                                    </div>
                                </div>
                                <p x-show="(exp.start_month && exp.start_month > todayMonth) || (exp.end_month && exp.end_month > todayMonth)"
                                    class="text-xs font-medium text-rose-600">Dates can't be in the future.</p>
                                <p x-show="exp.start_month && exp.end_month && exp.end_month < exp.start_month"
                                    class="text-xs font-medium text-rose-600">End month can't be before the start month.</p>
                                <p x-show="(exp.title.trim() || exp.organization.trim() || exp.start_month || exp.end_month) && !(exp.title.trim() && exp.organization.trim() && exp.start_month)"
                                    class="text-xs font-medium text-amber-600">Title, organization, and start month are all required.</p>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addExperience()"
                        class="flex items-center gap-1.5 text-sm font-medium text-red-900 hover:underline">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add another
                    </button>
                </div>

                {{-- Step 4: Education --}}
                <div x-show="step === 4" class="space-y-3">
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-900">Add your education</h3>
                        <p class="mt-1 text-sm text-gray-500">Share your academic background with your network.</p>
                    </div>
                    <div class="space-y-3">
                        <template x-for="(edu, i) in educations" :key="i">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-400"
                                        x-text="'Entry ' + (i + 1)"></span>
                                    <button type="button" @click="removeEducation(i)"
                                        x-show="educations.length > 1"
                                        class="text-xs font-medium text-rose-600 hover:underline">Remove</button>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <input x-model="edu.school" type="text" placeholder="School"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-900 focus:ring-red-900" />
                                    <input x-model="edu.degree" type="text" placeholder="Degree / Field"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-900 focus:ring-red-900" />
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="mb-1 block text-xs text-gray-500">Start date</label>
                                        <input x-model="edu.start_date" type="date" :max="todayDate"
                                            :class="edu.start_date && edu.start_date > todayDate ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-gray-300 focus:border-red-900 focus:ring-red-900'"
                                            class="block w-full rounded-lg text-sm shadow-sm" />
                                    </div>
                                    <div>
                                        <div class="mb-1 flex items-center justify-between">
                                            <label class="block text-xs text-gray-500">End date</label>
                                            <label class="flex items-center gap-1 text-xs font-medium text-gray-500">
                                                <input type="checkbox" x-model="edu.present" @change="edu.present && (edu.end_date = '')"
                                                    class="h-3.5 w-3.5 rounded border-gray-300 text-red-900 focus:ring-red-900" />
                                                Present
                                            </label>
                                        </div>
                                        <input x-show="!edu.present" x-model="edu.end_date" type="date"
                                            :class="edu.start_date && edu.end_date && edu.end_date < edu.start_date ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-gray-300 focus:border-red-900 focus:ring-red-900'"
                                            class="block w-full rounded-lg text-sm shadow-sm" />
                                        <div x-show="edu.present"
                                            class="flex items-center rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm font-medium text-gray-500">Present</div>
                                    </div>
                                </div>
                                <p x-show="edu.start_date && edu.start_date > todayDate"
                                    class="text-xs font-medium text-rose-600">Start date can't be in the future.</p>
                                <p x-show="edu.start_date && edu.end_date && edu.end_date < edu.start_date"
                                    class="text-xs font-medium text-rose-600">End date can't be before the start date.</p>
                                <p x-show="(edu.school.trim() || edu.degree.trim() || edu.start_date || edu.end_date) && !(edu.school.trim() && edu.degree.trim() && edu.start_date)"
                                    class="text-xs font-medium text-amber-600">School, degree, and start date are all required.</p>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addEducation()"
                        class="flex items-center gap-1.5 text-sm font-medium text-red-900 hover:underline">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add another
                    </button>
                </div>

                {{-- Step 5: Skills --}}
                <div x-show="step === 5" class="space-y-4">
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-900">Add your skills</h3>
                        <p class="mt-1 text-sm text-gray-500">Help others discover what you are good at.</p>
                    </div>
                    <div>
                        <input x-model="skills" type="text" placeholder="e.g. Laravel, UI Design, AutoCAD"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-900 focus:ring-red-900" />
                        <p class="mt-1 text-xs text-gray-400">Separate multiple skills with commas.</p>
                    </div>
                    <div x-show="skills.trim()" class="flex flex-wrap gap-1.5">
                        <template x-for="tag in skills.split(',').map(s => s.trim()).filter(s => s)" :key="tag">
                            <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-900 ring-1 ring-inset ring-red-200"
                                x-text="tag"></span>
                        </template>
                    </div>
                </div>

            </div>{{-- end scrollable content --}}

            {{-- Footer actions --}}
            <div class="shrink-0 flex gap-3 border-t border-gray-100 px-6 py-4">
                <button type="button" @click="skip()"
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Skip
                </button>
                <button type="button" @click="saveStep()" :disabled="saving || ! stepHasData() || hasDateError()"
                    class="flex-1 rounded-lg bg-red-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-50">
                    <span x-show="!saving" x-text="step < 5 ? 'Continue' : 'Done'"></span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>

        </div>

        {{-- Avatar crop modal (overlays the wizard) --}}
        <div x-show="cropOpen" x-cloak
            class="fixed inset-0 z-[650] flex items-center justify-center bg-black/70 p-4">
            <div class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl">
                <h4 class="text-base font-semibold text-gray-900">Adjust profile photo</h4>
                <p class="mt-1 text-xs text-gray-500">Drag the photo to reposition, use the slider to zoom.</p>

                <div class="mt-4 flex items-center justify-center">
                    <canvas x-ref="cropCanvas" width="320" height="320"
                        class="h-56 w-56 touch-none select-none rounded-full border border-gray-200 bg-gray-100"></canvas>
                </div>

                <div class="mt-4">
                    <label class="text-xs font-medium text-gray-700">Zoom</label>
                    <input x-ref="cropZoom" type="range" min="100" max="250" value="100"
                        class="mt-1 w-full accent-red-900" />
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <button type="button" @click="cancelCrop()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" @click="applyCrop()"
                        class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-800">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function profileSetupWizard() {
        return {
            open: true,
            step: 1,
            saving: false,
            persisted: false,

            cropOpen: false,
            cropper: null,
            avatarUrl: null,

            avatarFile: null,
            avatarPreview: '{{ $wizardAvatarUrl }}',

            bannerFile: null,
            bannerPreview: '{{ $wizardBannerUrl }}',

            experiences: [{ title: '', organization: '', start_month: '', end_month: '', present: false, description: '' }],
            educations:  [{ school: '', degree: '', start_date: '', end_date: '', present: false }],
            skills: '',

            // Local "today" for capping date inputs (no future starts).
            get todayDate() {
                const d = new Date();
                return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
            },
            get todayMonth() { return this.todayDate.slice(0, 7); },

            onAvatarPick(event) {
                const file = event.target.files?.[0];
                if (!file) return;

                if (this.avatarUrl) URL.revokeObjectURL(this.avatarUrl);
                this.avatarUrl = URL.createObjectURL(file);

                // Lazily build the cropper once so drag/zoom listeners aren't stacked.
                if (!this.cropper) {
                    this.cropper = window.createAvatarCropper({
                        canvas: this.$refs.cropCanvas,
                        zoomInput: this.$refs.cropZoom,
                        outputSize: 512,
                    });
                }

                this.cropper.reset();
                this.cropper.setImage(this.avatarUrl);
                this.cropOpen = true;
                event.target.value = ''; // allow re-picking the same file
            },

            applyCrop() {
                if (!this.cropper) return;
                this.cropper.toBlob((blob) => {
                    if (!blob) return;
                    this.avatarFile = new File([blob], 'avatar-cropped.png', { type: 'image/png' });
                    this.avatarPreview = URL.createObjectURL(blob);
                    this.cropOpen = false;
                });
            },

            cancelCrop() { this.cropOpen = false; },

            onBannerPick(event) {
                const file = event.target.files?.[0];
                if (!file) return;
                this.bannerFile = file;
                this.bannerPreview = URL.createObjectURL(file);
            },

            addExperience() {
                this.experiences.push({ title: '', organization: '', start_month: '', end_month: '', present: false, description: '' });
            },
            removeExperience(i) { this.experiences.splice(i, 1); },

            addEducation() {
                this.educations.push({ school: '', degree: '', start_date: '', end_date: '', present: false });
            },
            removeEducation(i) { this.educations.splice(i, 1); },

            skip() { this.advance(); },

            // Whether the current step holds enough data to "Continue" (save).
            // Empty steps must be passed with "Skip" instead.
            stepHasData() {
                if (this.step === 1) return !!this.avatarFile;
                if (this.step === 2) return !!this.bannerFile;
                if (this.step === 3) {
                    const filled = this.experiences.filter(e => e.title.trim() || e.organization.trim() || e.start_month || e.end_month);
                    return filled.length > 0 && filled.every(e => e.title.trim() && e.organization.trim() && e.start_month);
                }
                if (this.step === 4) {
                    const filled = this.educations.filter(e => e.school.trim() || e.degree.trim() || e.start_date || e.end_date);
                    return filled.length > 0 && filled.every(e => e.school.trim() && e.degree.trim() && e.start_date);
                }
                if (this.step === 5) return this.skills.trim() !== '';
                return false;
            },

            // True if any entry on the current step has an end date before its
            // start date. Zero-padded YYYY-MM / YYYY-MM-DD compare lexically.
            hasDateError() {
                if (this.step === 3) {
                    return this.experiences.some(e =>
                        (e.start_month && e.end_month && e.end_month < e.start_month) ||
                        (e.start_month && e.start_month > this.todayMonth) ||
                        (e.end_month && e.end_month > this.todayMonth)
                    );
                }
                if (this.step === 4) {
                    return this.educations.some(e =>
                        (e.start_date && e.end_date && e.end_date < e.start_date) ||
                        (e.start_date && e.start_date > this.todayDate)
                    );
                }
                return false;
            },

            // Close without persisting. Escaping on step 1 (before any engagement)
            // lets the wizard reappear once on the next login as a gentle nudge.
            close() { this.open = false; },

            // Persist completion (once) so the wizard never reappears on future
            // logins. Fired the moment the user engages (advances to step 2+) or
            // finishes the flow -- never on a step-1 escape.
            markComplete() {
                if (this.persisted) return;
                this.persisted = true;
                fetch('/profile/onboarding-complete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                }).catch(e => console.error('Onboarding complete error:', e));
            },

            async saveStep() {
                this.saving = true;
                try {
                    const fd = new FormData();
                    fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    fd.append('step', String(this.step));

                    if (this.step === 1) {
                        if (this.avatarFile) fd.append('avatar', this.avatarFile);
                    } else if (this.step === 2) {
                        if (this.bannerFile) fd.append('banner', this.bannerFile);
                    } else if (this.step === 3) {
                        this.experiences.forEach((exp, i) => {
                            fd.append(`experiences[${i}][title]`,        exp.title || '');
                            fd.append(`experiences[${i}][organization]`, exp.organization || '');
                            fd.append(`experiences[${i}][start_month]`,  exp.start_month || '');
                            fd.append(`experiences[${i}][end_month]`,    exp.end_month || '');
                            fd.append(`experiences[${i}][description]`,  exp.description || '');
                        });
                    } else if (this.step === 4) {
                        this.educations.forEach((edu, i) => {
                            fd.append(`educations[${i}][school]`,      edu.school || '');
                            fd.append(`educations[${i}][degree]`,      edu.degree || '');
                            fd.append(`educations[${i}][start_date]`,  edu.start_date || '');
                            fd.append(`educations[${i}][end_date]`,    edu.end_date || '');
                        });
                    } else if (this.step === 5) {
                        fd.append('skills', this.skills || '');
                    }

                    const res = await fetch('/profile/onboarding-step', { method: 'POST', body: fd });
                    if (res.ok) this.advance();
                } catch (e) {
                    console.error('Onboarding step error:', e);
                } finally {
                    this.saving = false;
                }
            },

            advance() {
                if (this.step < 5) {
                    this.step++;
                    // Reaching step 2+ means the user engaged -- lock it in.
                    if (this.step >= 2) this.markComplete();
                } else {
                    this.markComplete();
                    this.open = false;
                }
            },
        };
    }
    </script>
    @endif
</x-app-layout>

<x-footer />
