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
                        <p class="text-sm font-semibold text-amber-800">Registration submitted â€” pending admin review</p>
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
                        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Profile') }}</p>
                            <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</h3>
                            <p class="text-sm text-gray-600">{{ auth()->user()->program_course ?? __('Program pending') }}</p>
                            <div
                                class="mt-3 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ auth()->user()->accountStatusBadgeClass() }}">
                                {{ auth()->user()->accountStatusLabel() }}
                            </div>
                            <a href="{{ route('profiles.show', auth()->id()) }}"
                                class="mt-4 inline-flex items-center px-3 py-1 sm:px-5 sm:py-1.5 bg-red-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase whitespace-nowrap tracking-widest hover:bg-white hover:text-red-900 hover:border-red-900 focus:bg-white focus:text-red-900 focus:border-red-900 active:bg-white focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Open Profile') }}
                            </a>
                        </section>

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
                            $defaultCommunityId = old('community_id')
                                ?? ($joinedCommunitiesCollection->firstWhere('system_key', 'general-alumni-hub')->id ?? null);
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
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"
                            x-data="postComposer(@js($flairsByCommunity ?? []), {{ $defaultCommunityId ?? 'null' }})">
                            <button type="button"
                                @click="open = true"
                                class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-left text-sm text-gray-500 transition hover:bg-gray-100">
                                What's on your mind, {{ auth()->user()->name }}?
                            </button>

                            <div x-show="open"
                                x-transition.opacity
                                @keydown.escape.window="closeAndReset()"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                style="display: none;">
                                <div @click.away="closeAndReset()"
                                    class="w-full max-w-xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl flex flex-col max-h-[90vh]">

                                    <!-- Modal header -->
                                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
                                        <h3 class="text-sm font-semibold text-gray-900" x-text="editMode ? 'Edit post' : 'Create post'"></h3>
                                        <button type="button" @click="closeAndReset()"
                                            class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <form method="post" :action="editMode ? editUrl : '{{ route('posts.quick-store') }}'"
                                        enctype="multipart/form-data" class="flex flex-col overflow-y-auto flex-1"
                                        @submit.prevent="submitPost($el)">
                                        @csrf
                                        <input type="hidden" name="_method" :value="editMode ? 'PATCH' : 'POST'">
                                        <input type="hidden" name="community_id" :value="isConnectionsOnly ? '' : communityId">
                                        <input type="hidden" name="visibility" :value="visibility">
                                        <input type="hidden" name="post_type" :value="postType">
                                        <input type="hidden" name="title" :value="titleValue">
                                        <input type="hidden" name="body_markdown" :value="bodyValue">
                                        {{-- Event-only fields (ignored by validation for text/media posts) --}}
                                        <input type="hidden" name="event_type" :value="eventType">
                                        <input type="hidden" name="starts_at" :value="startsAtValue">
                                        <input type="hidden" name="ends_at" :value="endsAtValue">
                                        <input type="hidden" name="external_link" :value="externalLink">
                                        <input type="hidden" name="address" :value="address">
                                        <input type="hidden" name="venue" :value="venue">
                                        <input type="hidden" name="auto_invite" :value="autoInvite ? 1 : 0">

                                        <!-- Avatar + Name + Audience (stacked under name) -->
                                        <div class="flex items-start gap-3 px-5 pt-4 pb-3">
                                            <img src="{{ auth()->user()->profileAvatarUrl() }}"
                                                alt="{{ auth()->user()->name }}"
                                                class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover"
                                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-sm font-semibold text-gray-900 leading-none">{{ auth()->user()->name }}</span>

                                                <!-- Audience button under name -->
                                                <div class="relative">
                                                    <button type="button"
                                                        @click="audienceOpen = !audienceOpen"
                                                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                                        <template x-if="visibility === 'public'">
                                                            <svg class="h-3 w-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        </template>
                                                        <template x-if="visibility === 'connections'">
                                                            <svg class="h-3 w-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            </svg>
                                                        </template>
                                                        <template x-if="visibility === 'members'">
                                                            <svg class="h-3 w-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                            </svg>
                                                        </template>
                                                        <span x-text="{'public': 'Public', 'connections': 'Connections', 'members': 'Community'}[visibility]"></span>
                                                        <svg class="h-2.5 w-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>

                                                    <!-- Audience dropdown -->
                                                    <div x-show="audienceOpen"
                                                        @click.away="audienceOpen = false"
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="opacity-0 translate-y-1"
                                                        x-transition:enter-end="opacity-100 translate-y-0"
                                                        x-transition:leave="transition ease-in duration-75"
                                                        x-transition:leave-start="opacity-100 translate-y-0"
                                                        x-transition:leave-end="opacity-0 translate-y-1"
                                                        class="absolute left-0 top-full mt-1 w-72 rounded-xl border border-gray-200 bg-white shadow-lg z-20">
                                                        <div class="p-1.5">
                                                            <p class="px-2 pt-1.5 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">Who can see your post?</p>
                                                            <p x-show="isEvent" class="px-2 pb-1 text-[11px] text-gray-400">Events can be shared with your connections or a community only.</p>

                                                            <button type="button" @click="onVisibilityChange('public')"
                                                                x-show="!isEvent"
                                                                class="w-full flex items-start gap-3 rounded-lg px-3 py-2.5 text-left transition hover:bg-gray-50"
                                                                :class="{ 'bg-red-50 hover:bg-red-50': visibility === 'public' }">
                                                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100">
                                                                    <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                    </svg>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="flex items-center justify-between">
                                                                        <span class="text-sm font-medium text-gray-900">Public</span>
                                                                        <svg x-show="visibility === 'public'" class="h-4 w-4 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    </div>
                                                                    <p class="text-xs text-gray-500 mt-0.5">Anyone on AlumniHub can see this post</p>
                                                                </div>
                                                            </button>

                                                            <button type="button" @click="onVisibilityChange('connections')"
                                                                class="w-full flex items-start gap-3 rounded-lg px-3 py-2.5 text-left transition hover:bg-gray-50"
                                                                :class="{ 'bg-red-50 hover:bg-red-50': visibility === 'connections' }">
                                                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100">
                                                                    <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                    </svg>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="flex items-center justify-between">
                                                                        <span class="text-sm font-medium text-gray-900">Connections</span>
                                                                        <svg x-show="visibility === 'connections'" class="h-4 w-4 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    </div>
                                                                    <p class="text-xs text-gray-500 mt-0.5">Only people you are connected with will see this</p>
                                                                </div>
                                                            </button>

                                                            <button type="button" @click="onVisibilityChange('members')"
                                                                class="w-full flex items-start gap-3 rounded-lg px-3 py-2.5 text-left transition hover:bg-gray-50"
                                                                :class="{ 'bg-red-50 hover:bg-red-50': visibility === 'members' }">
                                                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100">
                                                                    <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                                    </svg>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="flex items-center justify-between">
                                                                        <span class="text-sm font-medium text-gray-900">Community</span>
                                                                        <svg x-show="visibility === 'members'" class="h-4 w-4 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    </div>
                                                                    <p class="text-xs text-gray-500 mt-0.5">Only members of the selected community can see this</p>
                                                                </div>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Community selector (hidden when connections-only) -->
                                        <div x-show="!isConnectionsOnly" class="px-5 pb-3">
                                            <select name="community_selector" x-model="communityId" aria-placeholder="Select a community"
                                                class="w-full rounded-md border border-gray-300 bg-white py-1.5 pl-3 pr-8 text-xs font-medium text-gray-700 shadow-sm focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900">
                                                <option value="" disabled selected hidden>Select a community</option>
                                                @foreach ($joinedCommunitiesCollection as $joinedCommunity)
                                                    <option value="{{ $joinedCommunity->id }}"
                                                        @selected($defaultCommunityId == $joinedCommunity->id)>
                                                        {{ $joinedCommunity->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Connections-only info banner -->
                                        <div x-show="isConnectionsOnly" style="display:none;" class="mx-5 mb-3 flex items-start gap-2.5 rounded-lg border border-blue-100 bg-blue-50 px-3.5 py-3">
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div class="text-xs text-blue-800 leading-relaxed">
                                                <span class="font-semibold">Connections only</span> â€” Your post will be visible only to people in your connections list. It will not appear in community feeds, public discovery, or to anyone outside your network.
                                            </div>
                                        </div>

                                        <!-- Post type selector (icons only, hidden in edit mode) -->
                                        <div class="px-5 pb-3" x-show="!editMode">
                                            <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">Post type</p>
                                            <div class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-gray-50 p-1">
                                                <button type="button" @click="setPostType('text')"
                                                    title="Text post" aria-label="Text post"
                                                    class="flex h-9 w-9 items-center justify-center rounded-lg transition"
                                                    :class="postType === 'text' ? 'bg-red-900 text-white shadow-sm' : 'text-gray-500 hover:bg-white hover:text-gray-700'">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/>
                                                    </svg>
                                                </button>
                                                <button type="button" @click="setPostType('media')"
                                                    title="Media post" aria-label="Media post"
                                                    class="flex h-9 w-9 items-center justify-center rounded-lg transition"
                                                    :class="postType === 'media' ? 'bg-red-900 text-white shadow-sm' : 'text-gray-500 hover:bg-white hover:text-gray-700'">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </button>
                                                <button type="button" @click="setPostType('event')"
                                                    title="Event post" aria-label="Event post"
                                                    class="flex h-9 w-9 items-center justify-center rounded-lg transition"
                                                    :class="postType === 'event' ? 'bg-red-900 text-white shadow-sm' : 'text-gray-500 hover:bg-white hover:text-gray-700'">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Divider -->
                                        <div class="mx-5 border-t border-gray-100 mb-3"></div>

                                        @php $inputClass = 'w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900'; @endphp

                                        <!-- Title input (text / media) -->
                                        <div x-show="!isEvent" class="px-5 pb-2">
                                            <input type="text" x-model="titleValue"
                                                class="w-full border-0 border-b border-gray-200 bg-transparent pb-1.5 text-sm font-semibold text-gray-900 placeholder:font-normal placeholder:text-gray-400 focus:border-gray-400 focus:outline-none focus:ring-0"
                                                placeholder="Add a title (optional)">
                                        </div>

                                        <!-- Body area (text / media) -->
                                        <div x-show="!isEvent" class="px-5 pb-3 flex flex-col gap-2">
                                            <textarea x-model="bodyValue" :required="!isEvent" :rows="isMedia ? 2 : 4"
                                                class="w-full border-0 bg-transparent text-sm text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-0 resize-none leading-relaxed"
                                                placeholder="What's on your mind, {{ auth()->user()->name }}?"></textarea>
                                        </div>

                                        <!-- Event sub-form -->
                                        <div x-show="isEvent" class="px-5 pb-3 flex flex-col gap-3" style="display:none;">
                                            <!-- Event type: online / in person -->
                                            <div>
                                                <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">Event type</p>
                                                <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
                                                    <button type="button" @click="eventType = 'online'"
                                                        class="rounded-md px-4 py-1.5 text-sm font-medium transition flex gap-1.5 items-center"
                                                        :class="eventType === 'online' ? 'bg-red-900 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'">
                                                        <i class="fas fa-globe"></i><span>Online</span>
                                                    </button>
                                                    <button type="button" @click="eventType = 'in_person'"
                                                        class="rounded-md px-4 py-1.5 text-sm font-medium transition flex gap-1.5 items-center"
                                                        :class="eventType === 'in_person' ? 'bg-red-900 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'">
                                                        <i class="fas fa-map-marker-alt"></i><span>In person</span>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Event name -->
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">Event name <span class="text-red-500">*</span></label>
                                                <input type="text" x-model="titleValue" :required="isEvent"
                                                    placeholder="e.g. Alumni Homecoming 2026" class="{{ $inputClass }}">
                                            </div>

                                            <!-- Start date / time -->
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">Start date <span class="text-red-500">*</span></label>
                                                    <input type="date" x-model="startDate" :required="isEvent" @change="eventDateError = false" class="{{ $inputClass }}">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">Start time <span class="text-red-500">*</span></label>
                                                    <input type="time" x-model="startTime" :required="isEvent" @change="eventDateError = false" class="{{ $inputClass }}">
                                                </div>
                                            </div>

                                            <p x-show="eventDateError" class="text-xs font-medium text-red-600">Start date and time must be in the future.</p>

                                            <!-- Add end date toggle -->
                                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                                <input type="checkbox" x-model="hasEndDate"
                                                    class="h-4 w-4 rounded border-gray-300 text-red-900 accent-red-900 focus:ring-red-900">
                                                Add end date and time
                                            </label>

                                            <!-- End date / time -->
                                            <div x-show="hasEndDate" style="display:none;" class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">End date</label>
                                                    <input type="date" x-model="endDate" :required="isEvent && hasEndDate" class="{{ $inputClass }}">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">End time</label>
                                                    <input type="time" x-model="endTime" :required="isEvent && hasEndDate" class="{{ $inputClass }}">
                                                </div>
                                            </div>

                                            <!-- Address + venue (in person only) -->
                                            <template x-if="eventType === 'in_person'">
                                                <div class="flex flex-col gap-3">
                                                    <div>
                                                        <label class="mb-1 block text-xs font-medium text-gray-600">Address <span class="text-red-500">*</span></label>
                                                        <input type="text" x-model="address" :required="isEvent && eventType === 'in_person'"
                                                            placeholder="e.g. street, city, postal code" class="{{ $inputClass }}">
                                                    </div>
                                                    <div>
                                                        <label class="mb-1 block text-xs font-medium text-gray-600">Venue <span class="text-gray-400 font-normal">(optional)</span></label>
                                                        <input type="text" x-model="venue"
                                                            placeholder="e.g. floor / room number" class="{{ $inputClass }}">
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- External event link -->
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                                    External event link
                                                    <template x-if="eventType === 'online'"><span class="text-red-500">*</span></template>
                                                    <template x-if="eventType === 'in_person'"><span class="text-gray-400 font-normal">(optional)</span></template>
                                                </label>
                                                <input type="url" x-model="externalLink" :required="isEvent && eventType === 'online'"
                                                    placeholder="https://" class="{{ $inputClass }}">
                                            </div>

                                            <!-- Description -->
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                                                <textarea x-model="bodyValue" rows="3"
                                                    placeholder="Add details about your event" class="{{ $inputClass }} resize-none"></textarea>
                                            </div>

                                            <!-- Email auto-invites -->
                                            <label class="flex items-start gap-2.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 cursor-pointer">
                                                <input type="checkbox" x-model="autoInvite"
                                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-red-900 accent-red-900 focus:ring-red-900">
                                                <span class="text-xs leading-relaxed text-gray-600">
                                                    <span class="font-semibold text-gray-800">Email auto-invites</span><br>
                                                    Invite everyone who can see this event â€” your
                                                    <span x-text="visibility === 'connections' ? 'connections' : 'community members'"></span>
                                                    get an email and an in-app notification.
                                                </span>
                                            </label>
                                        </div>

                                        <!-- Shared image block (media + event) -->
                                        <div x-show="isMedia || isEvent" style="display:none;" class="px-5 pb-3 flex flex-col gap-2">
                                            <!-- Existing media (edit mode) with per-image remove -->
                                            <template x-if="editMode && visibleExistingMedia.length > 0">
                                                <div>
                                                    <p class="mb-1.5 text-xs font-medium text-gray-500">Current images</p>
                                                    <div :class="visibleExistingMedia.length === 1 ? '' : 'grid grid-cols-2 gap-2'">
                                                        <template x-for="m in visibleExistingMedia" :key="m.id">
                                                            <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                                                <img :src="m.url" alt=""
                                                                    :class="visibleExistingMedia.length === 1 ? 'w-full max-h-72 object-contain' : 'w-full h-40 object-cover'">
                                                                <button type="button" @click="removedMediaIds.push(m.id)"
                                                                    class="absolute top-2 right-2 flex h-7 w-7 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/75 transition"
                                                                    aria-label="Remove image">
                                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-for="id in removedMediaIds" :key="id">
                                                <input type="hidden" name="removed_media[]" :value="id">
                                            </template>

                                            <!-- Image preview (Facebook-style) -->
                                            <div id="imagePreviewContainer" class="hidden relative rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
                                                <button type="button" id="removeImageBtn"
                                                    class="absolute top-2 right-2 z-10 flex h-7 w-7 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/75 transition">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                                <img id="singleImagePreview" src="" alt="" class="hidden w-full max-h-56 object-cover">
                                                <div id="multiImageGrid" class="hidden gap-0.5"></div>
                                            </div>

                                            <!-- Attach image button -->
                                            <label class="self-start cursor-pointer inline-flex items-center gap-1.5 rounded-md border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                                                <svg class="h-3.5 w-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                Attach Image
                                                <input type="file" name="attachments[]" id="imageUploadInput" multiple accept="image/*"
                                                    @change="handleImageUpload($event)"
                                                    class="sr-only">
                                            </label>
                                        </div>

                                        <!-- Flair tags (required, 1â€“3, hidden when connections-only or event) -->
                                        <template x-if="filteredFlairs.length > 0 && !isConnectionsOnly && !isEvent">
                                            <div class="px-5 pb-3 pt-3 border-t border-gray-100">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                        Flair tag <span class="text-red-500 normal-case font-normal">* required</span>
                                                    </span>
                                                    <span class="text-xs text-gray-400" x-text="`${selectedFlairs.length} / 3 selected`"></span>
                                                </div>

                                                <p x-show="flairError" class="mb-2 text-xs font-medium text-red-600">Please select at least 1 flair tag (max 3).</p>

                                                <div class="flex flex-wrap gap-1.5">
                                                    <template x-for="flair in visibleFlairs" :key="flair.id">
                                                        <button type="button"
                                                            @click="toggleFlair(flair.id)"
                                                            :disabled="!canSelectFlair(flair.id)"
                                                            class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium transition"
                                                            :class="{
                                                                'border-red-900 bg-red-900 text-white shadow-sm': selectedFlairs.includes(flair.id),
                                                                'border-gray-300 bg-white text-gray-700 hover:border-gray-400 hover:bg-gray-50': !selectedFlairs.includes(flair.id) && canSelectFlair(flair.id),
                                                                'border-gray-200 bg-gray-50 text-gray-300 cursor-not-allowed': !canSelectFlair(flair.id)
                                                            }">
                                                            <span x-show="flair.icon" x-text="flair.icon" class="leading-none"></span>
                                                            <span x-text="flair.name"></span>
                                                        </button>
                                                    </template>

                                                    <template x-if="filteredFlairs.length > 4">
                                                        <button type="button" @click="flairsExpanded = !flairsExpanded"
                                                            class="inline-flex items-center gap-1 rounded-full border border-dashed border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-500 hover:bg-gray-50 transition">
                                                            <span x-text="flairsExpanded ? 'Show less' : `+${filteredFlairs.length - 4} more`"></span>
                                                        </button>
                                                    </template>
                                                </div>

                                                <!-- Hidden inputs for form submission -->
                                                <template x-for="id in selectedFlairs" :key="id">
                                                    <input type="hidden" name="flairs[]" :value="id">
                                                </template>
                                            </div>
                                        </template>

                                        <!-- Footer -->
                                        <div class="border-t border-gray-100 px-5 py-3 flex items-center justify-end">
                                            <button type="submit"
                                                :disabled="isSubmitting"
                                                :class="isSubmitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-red-800'"
                                                class="rounded-lg bg-red-900 px-7 py-2 text-sm font-semibold text-white transition">
                                                <span x-show="!isSubmitting" x-text="editMode ? 'Save changes' : 'Post'"></span>
                                                <span x-show="isSubmitting" x-text="editMode ? 'Saving…' : 'Posting…'"></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>{{-- end composer card --}}
                        @endif

                        <div x-data="feedController(@js(($availableFlairs ?? collect())->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'icon' => $f->icon])->values()), @js($selectedFlairIds ?? []), {{ isset($posts) && $posts->hasMorePages() ? 'true' : 'false' }}, {{ isset($posts) ? $posts->currentPage() : 1 }})"
                            x-init="initScroll()" class="space-y-3">

                        {{-- Flair feed filter --}}
                        @if(isset($availableFlairs) && $availableFlairs->isNotEmpty())
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Filter by topic</span>
                                    <svg x-show="loading" class="h-3.5 w-3.5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24" style="display:none;">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400" x-show="selected.length > 0" x-text="`${selected.length}/3 active`" style="display:none;"></span>
                                    <button x-show="selected.length > 0" @click="clearAll()"
                                        class="text-xs font-medium text-red-900 hover:underline" style="display:none;">Clear</button>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="flair in visibleFlairs" :key="flair.id">
                                    <button type="button"
                                        @click="toggle(flair.id)"
                                        :disabled="!canSelect(flair.id)"
                                        class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium transition"
                                        :class="{
                                            'border-red-900 bg-red-900 text-white shadow-sm': isSelected(flair.id),
                                            'border-gray-300 bg-white text-gray-700 hover:border-gray-400 hover:bg-gray-50': !isSelected(flair.id) && canSelect(flair.id),
                                            'border-gray-200 bg-gray-50 text-gray-300 cursor-not-allowed': !canSelect(flair.id)
                                        }">
                                        <span x-show="flair.icon" x-text="flair.icon" class="leading-none"></span>
                                        <span x-text="flair.name"></span>
                                    </button>
                                </template>
                                <template x-if="flairs.length > 8">
                                    <button type="button" @click="expanded = !expanded"
                                        class="inline-flex items-center gap-1 rounded-full border border-dashed border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-500 hover:bg-gray-50 transition">
                                        <span x-text="expanded ? 'Show less' : `+${flairs.length - 8} more`"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        @endif

                        <div id="feed-posts-container" class="space-y-3">
                        @if(isset($posts))
                            @include('partials.feed-posts', ['posts' => $posts])
                        @else
                            @foreach ($feedCards as $card)
                                <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                                    <div class="p-5">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h3 class="text-base font-semibold text-gray-900">{{ $card['author'] }}</h3>
                                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ $card['meta'] }}</p>
                                            </div>
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                                {{ __('Placeholder') }}
                                            </span>
                                        </div>

                                        <p class="mt-4 text-sm leading-6 text-gray-700">{{ $card['content'] }}</p>
                                    </div>

                                    <div class="mt-5 flex flex-wrap gap-2 border-t border-gray-100 px-5 py-3 text-xs">
                                        <button type="button" disabled class="rounded-md border border-gray-200 px-3 py-1 font-semibold text-gray-500">{{ __('Like') }}</button>
                                        <button type="button" disabled class="rounded-md border border-gray-200 px-3 py-1 font-semibold text-gray-500">{{ __('Comment') }}</button>
                                        <button type="button" disabled class="rounded-md border border-gray-200 px-3 py-1 font-semibold text-gray-500">{{ __('Share') }}</button>
                                    </div>
                                </article>
                            @endforeach
                        @endif

                        </div>{{-- end feed-posts-container --}}

                        {{-- Infinite scroll sentinel + states --}}
                        <div x-ref="sentinel" aria-hidden="true" class="h-px"></div>
                        <div x-show="loadingMore" class="flex justify-center py-6" style="display:none;">
                            <svg class="h-6 w-6 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                        </div>
                        <div x-show="reachedEnd" class="py-6 text-center text-xs text-gray-400" style="display:none;">
                            You're all caught up
                        </div>
                        </div>{{-- end feedController --}}

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

    <script>
        function postComposer(flairsByCommunity, defaultCommunityId) {
            return {
                open: false,
                editMode: false,
                editUrl: null,
                communityId: defaultCommunityId ? String(defaultCommunityId) : '',
                flairsByCommunity,
                visibility: 'members',
                audienceOpen: false,
                selectedFlairs: [],
                flairsExpanded: false,
                flairError: false,
                eventDateError: false,
                isSubmitting: false,

                // Edit mode: existing post media + ids the user removed
                existingMedia: [],
                removedMediaIds: [],

                // Post type + shared title/body
                postType: 'text', // text | media | event
                titleValue: '',
                bodyValue: '',

                // Event fields
                eventType: 'online', // online | in_person
                startDate: '',
                startTime: '',
                hasEndDate: false,
                endDate: '',
                endTime: '',
                externalLink: '',
                address: '',
                venue: '',
                autoInvite: false,

                get isText() { return this.postType === 'text'; },
                get isMedia() { return this.postType === 'media'; },
                get isEvent() { return this.postType === 'event'; },
                get visibleExistingMedia() { return this.existingMedia.filter(m => !this.removedMediaIds.includes(m.id)); },

                get startsAtValue() {
                    return this.startDate && this.startTime ? `${this.startDate} ${this.startTime}` : '';
                },
                get endsAtValue() {
                    return this.hasEndDate && this.endDate && this.endTime ? `${this.endDate} ${this.endTime}` : '';
                },

                setPostType(type) {
                    this.postType = type;
                    if (type === 'event' && this.visibility === 'public') {
                        this.visibility = 'members';
                    }
                },

                init() {
                    window.addEventListener('open-edit-composer', (e) => {
                        const d = e.detail;
                        this.editMode    = true;
                        this.editUrl     = d.editUrl;
                        this.postType    = d.postType ?? 'text';
                        this.titleValue  = d.title ?? '';
                        this.bodyValue   = d.body ?? '';
                        this.visibility  = d.visibility ?? 'members';
                        this.communityId = d.communityId ? String(d.communityId) : '';
                        this.selectedFlairs  = Array.isArray(d.flairs) ? d.flairs.map(Number) : [];
                        this.existingMedia   = Array.isArray(d.media) ? d.media : [];
                        this.removedMediaIds = [];
                        this.resetImageUpload();

                        if (d.event) {
                            this.eventType  = d.event.event_type ?? 'online';
                            const sa        = d.event.starts_at ? d.event.starts_at.split('T') : ['', ''];
                            const ea        = d.event.ends_at   ? d.event.ends_at.split('T')   : ['', ''];
                            this.startDate  = sa[0] ?? '';
                            this.startTime  = sa[1] ?? '';
                            this.hasEndDate = !!d.event.ends_at;
                            this.endDate    = ea[0] ?? '';
                            this.endTime    = ea[1] ?? '';
                            this.externalLink = d.event.external_link ?? '';
                            this.address    = d.event.address ?? '';
                            this.venue      = d.event.venue   ?? '';
                        }

                        this.open = true;
                    });
                },

                closeAndReset() {
                    this.open            = false;
                    this.editMode        = false;
                    this.editUrl         = null;
                    this.existingMedia   = [];
                    this.removedMediaIds = [];
                    this.selectedFlairs  = [];
                    this.resetImageUpload();
                },

                resetImageUpload() {
                    const input = document.getElementById('imageUploadInput');
                    const container = document.getElementById('imagePreviewContainer');
                    const single = document.getElementById('singleImagePreview');
                    const grid = document.getElementById('multiImageGrid');
                    if (input) input.value = '';
                    if (container) container.classList.add('hidden');
                    if (single) { single.src = ''; single.classList.add('hidden'); }
                    if (grid) { grid.innerHTML = ''; grid.className = 'hidden gap-0.5'; }
                },

                get isConnectionsOnly() {
                    return this.visibility === 'connections';
                },

                get filteredFlairs() {
                    const global = this.flairsByCommunity['global'] || [];
                    const community = this.communityId && !this.isConnectionsOnly
                        ? (this.flairsByCommunity[String(this.communityId)] || [])
                        : [];
                    const seen = new Set();
                    return [...global, ...community].filter(f => {
                        if (seen.has(f.id)) return false;
                        seen.add(f.id);
                        return true;
                    });
                },

                get visibleFlairs() {
                    return this.flairsExpanded ? this.filteredFlairs : this.filteredFlairs.slice(0, 4);
                },

                toggleFlair(id) {
                    const idx = this.selectedFlairs.indexOf(id);
                    if (idx >= 0) {
                        this.selectedFlairs.splice(idx, 1);
                    } else if (this.selectedFlairs.length < 3) {
                        this.selectedFlairs.push(id);
                    }
                    this.flairError = false;
                },

                canSelectFlair(id) {
                    return this.selectedFlairs.includes(id) || this.selectedFlairs.length < 3;
                },

                onVisibilityChange(newVisibility) {
                    this.visibility = newVisibility;
                    this.audienceOpen = false;
                    if (newVisibility === 'connections') {
                        this.communityId = '';
                        this.selectedFlairs = [];
                    }
                },

                handleImageUpload(event) {
                    const files = event.target.files;
                    const container = document.getElementById('imagePreviewContainer');
                    const single = document.getElementById('singleImagePreview');
                    const grid = document.getElementById('multiImageGrid');
                    const removeBtn = document.getElementById('removeImageBtn');

                    if (!files || files.length === 0) {
                        container.classList.add('hidden');
                        return;
                    }

                    container.classList.remove('hidden');

                    if (files.length === 1) {
                        grid.classList.add('hidden');
                        single.classList.remove('hidden');
                        const reader = new FileReader();
                        reader.onload = e => { single.src = e.target.result; };
                        reader.readAsDataURL(files[0]);
                    } else {
                        single.classList.add('hidden');
                        grid.classList.remove('hidden');
                        const show = Math.min(files.length, 4);
                        grid.className = `grid gap-0.5 ${show === 2 ? 'grid-cols-2' : show === 3 ? 'grid-cols-3' : 'grid-cols-2'}`;
                        grid.innerHTML = '';
                        Array.from(files).slice(0, 4).forEach((file, i) => {
                            const wrap = document.createElement('div');
                            wrap.className = 'relative';
                            const img = document.createElement('img');
                            img.className = 'w-full h-32 object-cover';
                            const reader = new FileReader();
                            reader.onload = e => { img.src = e.target.result; };
                            reader.readAsDataURL(file);
                            wrap.appendChild(img);
                            if (i === 3 && files.length > 4) {
                                const overlay = document.createElement('div');
                                overlay.className = 'absolute inset-0 flex items-center justify-center bg-black/50 text-white font-semibold text-lg';
                                overlay.textContent = `+${files.length - 4}`;
                                wrap.appendChild(overlay);
                            }
                            grid.appendChild(wrap);
                        });
                    }

                    removeBtn.onclick = () => {
                        container.classList.add('hidden');
                        single.src = '';
                        grid.innerHTML = '';
                        grid.className = 'hidden gap-0.5';
                        event.target.value = '';
                    };
                },

                submitPost(form) {
                    if (this.isSubmitting) return;

                    if (this.isEvent) {
                        const starts = this.startsAtValue ? new Date(this.startsAtValue) : null;
                        if (!starts || starts <= new Date()) {
                            this.eventDateError = true;
                            return;
                        }
                        this.eventDateError = false;
                    }

                    // Flairs aren't edited via the composer, so skip the requirement in edit mode.
                    if (!this.editMode && !this.isEvent && this.filteredFlairs.length > 0 && !this.isConnectionsOnly && this.selectedFlairs.length === 0) {
                        this.flairError = true;
                        return;
                    }
                    this.flairError = false;
                    this.isSubmitting = true;
                    form.submit();
                }
            };
        }

        function feedManager() {
            return {
                showModal: false,
                selectedPostId: null,
                apiUrl: null,
                commentsUrl: null,

                openPostModal(event, postId, apiUrl, commentsUrl) {
                    event?.preventDefault();
                    this.selectedPostId = postId;
                    this.apiUrl = apiUrl;
                    this.commentsUrl = commentsUrl;
                    this.showModal = true;
                    window.dispatchEvent(new CustomEvent('post-modal-opened', {
                        detail: { postId, apiUrl, commentsUrl }
                    }));
                },

                closeModal() {
                    this.showModal = false;
                    this.selectedPostId = null;
                    this.apiUrl = null;
                    this.commentsUrl = null;
                }
            };
        }

        function postCard(postId, initialLikeCount, initialCommentCount, apiUrl, likeUrl, isInitiallyLiked = false, meta = {}) {
            return {
                postId,
                likeCount: initialLikeCount,
                commentCount: initialCommentCount,
                apiUrl,
                likeUrl,
                isLiked: isInitiallyLiked,
                isLikingLoading: false,
                isBodyExpanded: false,
                isBodyOverflowing: false,

                // Editable fields kept in sync with the detail modal (post-updated event).
                visibility: meta.visibility ?? 'members',
                title: meta.title ?? '',
                bodyText: meta.body ?? '',

                get visibilityLabel() {
                    return { public: 'Public', connections: 'Connections', members: 'Members' }[this.visibility] || 'Members';
                },
                get visibilityClass() {
                    return {
                        public: 'bg-green-50 text-green-700 ring-green-200',
                        connections: 'bg-blue-50 text-blue-700 ring-blue-200',
                        members: 'bg-gray-100 text-gray-600 ring-gray-200',
                    }[this.visibility] || 'bg-gray-100 text-gray-600 ring-gray-200';
                },

                init() {
                    window.addEventListener('post-updated', (event) => {
                        if (Number(event?.detail?.postId) !== Number(this.postId)) return;
                        const d = event.detail;
                        if (d.visibility) this.visibility = d.visibility;
                        if ('title' in d) this.title = d.title ?? '';
                        if ('body' in d) { this.bodyText = d.body ?? ''; this.refreshBodyOverflow(); }
                    });

                    window.addEventListener('post-comment-count-changed', (event) => {
                        const postId = Number(event?.detail?.postId);
                        const count = Number(event?.detail?.count);
                        if (!Number.isFinite(postId) || !Number.isFinite(count)) return;
                        if (postId !== Number(this.postId)) return;
                        this.commentCount = count;
                    });

                    // Keep the feed card's like state in sync with the detail modal
                    // (both when liking inside it and when it loads the true count).
                    window.addEventListener('post-like-count-changed', (event) => {
                        if (Number(event?.detail?.postId) !== Number(this.postId)) return;
                        const count = Number(event?.detail?.count);
                        if (Number.isFinite(count)) this.likeCount = count;
                        if (typeof event?.detail?.liked === 'boolean') this.isLiked = event.detail.liked;
                    });

                    this.refreshBodyOverflow();
                },

                refreshBodyOverflow() {
                    this.isBodyOverflowing = false;
                    if (this.isBodyExpanded) return;
                    this.$nextTick(() => {
                        const el = this.$refs?.postBody;
                        if (!el) return;
                        this.isBodyOverflowing = el.scrollHeight > el.clientHeight + 2;
                    });
                },

                toggleBody() {
                    this.isBodyExpanded = !this.isBodyExpanded;
                    if (!this.isBodyExpanded) this.refreshBodyOverflow();
                },

                openPostModal(event) {
                    // Both article and comment button call this; buttons use @click.stop so
                    // only non-button areas bubble up through the article click handler.
                    // Community-less posts (connections-only) have no detail API route.
                    if (!this.apiUrl) return;
                    const commentsUrl = this.apiUrl.replace('/api', '/comments');
                    window.dispatchEvent(new CustomEvent('post-modal-opened', {
                        detail: { postId: this.postId, apiUrl: this.apiUrl, commentsUrl }
                    }));
                },

                toggleLike() {
                    if (this.isLikingLoading || !this.likeUrl) return;
                    this.isLikingLoading = true;

                    fetch(this.likeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        },
                    })
                        .then(response => {
                            if (!response.ok) throw new Error('Failed to like post');
                            return response.json();
                        })
                        .then(data => {
                            this.isLiked = data.liked;
                            this.likeCount = data.like_count;
                            this.isLikingLoading = false;
                            window.dispatchEvent(new CustomEvent('post-like-count-changed', {
                                detail: { postId: this.postId, count: this.likeCount, liked: this.isLiked }
                            }));
                        })
                        .catch(err => {
                            console.error('Error liking post:', err);
                            this.isLikingLoading = false;
                        });
                }
            };
        }

        function feedController(availableFlairs, selectedIds, initialHasMore, initialPage) {
            return {
                flairs: availableFlairs,
                selected: selectedIds,
                expanded: false,
                loading: false,       // filter change (replace) in progress
                loadingMore: false,   // infinite-scroll append in progress
                page: initialPage,    // last page loaded into the feed
                hasMore: initialHasMore,
                observer: null,

                get visibleFlairs() {
                    return this.expanded ? this.flairs : this.flairs.slice(0, 8);
                },
                get reachedEnd() {
                    return !this.hasMore && !this.loadingMore && !this.loading;
                },
                isSelected(id) { return this.selected.includes(id); },
                canSelect(id) { return this.isSelected(id) || this.selected.length < 3; },

                buildQuery(page) {
                    const params = new URLSearchParams();
                    this.selected.forEach(id => params.append('flairs[]', id));
                    if (page > 1) params.set('page', page);
                    return params.toString();
                },

                toggle(id) {
                    if (this.isSelected(id)) {
                        this.selected = this.selected.filter(s => s !== id);
                    } else if (this.selected.length < 3) {
                        this.selected = [...this.selected, id];
                    } else {
                        return;
                    }
                    this.applyFilter();
                },
                clearAll() {
                    this.selected = [];
                    this.applyFilter();
                },

                // Flair change: reset to page 1 and replace the feed.
                applyFilter() {
                    const url = new URL(window.location.href);
                    url.search = '';
                    this.selected.forEach(id => url.searchParams.append('flairs[]', id));
                    history.pushState({}, '', url.toString());

                    this.loading = true;
                    this.page = 1;
                    fetch('/feed/posts?' + this.buildQuery(1), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.json())
                        .then(data => {
                            document.getElementById('feed-posts-container').innerHTML = data.html;
                            this.hasMore = data.hasMore;
                            this.loading = false;
                            this.$nextTick(() => this.fillViewport());
                        })
                        .catch(() => { this.loading = false; });
                },

                // Infinite scroll: fetch the next page and append.
                loadMore() {
                    if (this.loadingMore || this.loading || !this.hasMore) return;
                    this.loadingMore = true;
                    const next = this.page + 1;
                    fetch('/feed/posts?' + this.buildQuery(next), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.json())
                        .then(data => {
                            document.getElementById('feed-posts-container').insertAdjacentHTML('beforeend', data.html);
                            this.page = next;
                            this.hasMore = data.hasMore;
                            this.loadingMore = false;
                            this.$nextTick(() => this.fillViewport());
                        })
                        .catch(() => { this.loadingMore = false; });
                },

                // Keep loading while the sentinel is still on-screen (short feeds).
                fillViewport() {
                    const s = this.$refs.sentinel;
                    if (!s || !this.hasMore) return;
                    if (s.getBoundingClientRect().top < window.innerHeight) this.loadMore();
                },

                initScroll() {
                    this.observer = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting) this.loadMore();
                    }, { rootMargin: '400px' });
                    this.observer.observe(this.$refs.sentinel);
                }
            };
        }
    </script>

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
                        {{ __('Welcome to AlumniHub! Your account is currently unverified, so you can only browse public posts. Verify your alumni status to unlock the full experience â€” post, like, comment, join community discussions, and connect with your batchmates.') }}
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
