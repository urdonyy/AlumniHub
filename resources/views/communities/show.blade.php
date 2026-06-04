<x-app-layout>
    <x-slot name="title">{{ $community->name }}</x-slot>
    <x-slot name="header">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1.5 text-sm">
            <a href="{{ route('communities.index') }}" class="font-semibold text-red-900/70 transition hover:text-red-900">{{ __('Communities') }}</a>
            <svg class="h-4 w-4 shrink-0 text-red-900/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="font-semibold text-red-900">{{ $community->name }}</span>
        </nav>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8"
            x-data="communityMembersModal('{{ route('communities.members', $community) }}', {{ $isVerified ? 'true' : 'false' }})">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="space-y-5 px-6 py-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-2xl font-semibold text-gray-900">{{ $community->name }}</h3>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ $community->description ?? __('No description provided yet.') }}
                            </p>
                        </div>

                        <div
                            class="inline-flex shrink-0 items-center self-start whitespace-nowrap rounded-full px-3 py-1 text-xs sm:text-sm font-semibold ring-1 ring-inset {{ $user->communityAccessBadgeClass() }}">
                            {{ $user->communityAccessLabel() }}
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <button type="button" @click="openMembers()"
                            class="group rounded-lg bg-gray-50 px-4 py-3 text-left text-sm text-gray-700 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Members') }}</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $community->members_count }}</p>
                        </button>

                        <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Posts') }}</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $postCount }}</p>
                        </div>

                        <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('Created by') }}
                            </p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">
                                {{ $community->creator?->name ?? __('System') }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('Membership') }}
                            </p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">
                                @if ($isMember)
                                    {{ __('Joined') }}
                                @elseif ($community->is_system)
                                    {{-- System program communities are auto-assigned; you can still interact
                                         with their public posts, you just can't join. --}}
                                    {{ __('Not eligible') }}
                                @else
                                    {{ __('Not joined') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($community->rules as $rule)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                {{ $rule->batch_year ? 'Batch ' . $rule->batch_year : __('Any batch') }}
                                ·
                                {{ $rule->program_course ?? __('Any program') }}
                            </span>
                        @endforeach
                    </div>

                    @if (session('status') === 'join-request-submitted')
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ __('Your join request was submitted. Moderators will be notified.') }}
                        </div>
                    @endif
                    @if (session('status') === 'already-a-member')
                        <div class="rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            {{ __('You are already a member of this community.') }}
                        </div>
                    @endif
                    @if (session('status') === 'member-removed')
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ __('Member removed.') }}
                        </div>
                    @endif
                    @if ($errors->has('member'))
                        <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            {{ $errors->first('member') }}
                        </div>
                    @endif
                    @if ($errors->has('join_request'))
                        <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            {{ $errors->first('join_request') }}
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-5">
                        @if ($canInteract)
                            @if ($community->is_system)
                                @if ($isMember)
                                    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                                        {{ __('You were automatically added to this community at registration. Membership is managed by the system.') }}
                                    </div>
                                @else
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                        {{ __('You can view this community and its public posts, but you cannot join other program communities. Your program community was assigned automatically when you registered.') }}
                                    </div>
                                @endif
                            @elseif ($isMember)
                                <form method="post" action="{{ route('communities.leave', $community) }}">
                                    @csrf
                                    @method('delete')
                                    <x-primary-button>{{ __('Leave Community') }}</x-primary-button>
                                </form>
                            @elseif ($community->isProgramBatch())
                                @if ($pendingJoinRequest)
                                    <span class="inline-flex items-center rounded-md bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-800">
                                        {{ __('Join request pending') }}
                                    </span>
                                @else
                                    <form method="post" action="{{ route('communities.join', $community) }}">
                                        @csrf
                                        <x-primary-button>{{ __('Request to join') }}</x-primary-button>
                                    </form>
                                    @if ($otherProgramBatch)
                                        <div class="w-full text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">
                                            {{ __('Heads up: you are already a member of program-batch community ":n". A moderator here will not be able to accept you until you leave it.', ['n' => $otherProgramBatch->name]) }}
                                        </div>
                                    @endif
                                @endif
                            @else
                                <form method="post" action="{{ route('communities.join', $community) }}">
                                    @csrf
                                    <x-primary-button>{{ __('Join Community') }}</x-primary-button>
                                </form>
                            @endif
                        @else
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                {{ __('This community is read-only until your account is verified. You can browse it, but posting and membership actions are disabled.') }}
                            </div>
                        @endif

                        @if ($user->canManageCommunities())
                            <a href="{{ route('admin.communities.index') }}"
                                class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-600">
                                {{ __('Manage Communities') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Activity feed: composer + flair filter + infinite scroll, scoped to this community --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900">{{ __('Activity') }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Recent posts in this community.') }}</p>
                    </div>
                    <span class="shrink-0 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">
                        {{ $postCount }}
                    </span>
                </div>

                @if ($isMember && $isVerified)
                    @include('partials.post-composer', [
                        'flairsByCommunity' => $flairsByCommunity,
                        'defaultCommunityId' => $community->id,
                        'composerLockedCommunityId' => $community->id,
                        {{-- General Alumni Hub auto-joins everyone, so Public and Community reach the same people —
                             show a single audience there (avoids the redundant picker). Other communities offer both. --}}
                        'composerVisibilities' => $community->system_key === 'general-alumni-hub' ? ['members'] : ['public', 'members'],
                        'composerGeneralHubId' => $community->system_key === 'general-alumni-hub' ? $community->id : null,
                        'joinedCommunitiesCollection' => collect(),
                    ])
                @endif

                @include('partials.feed-region', [
                    'posts' => $communityFeed,
                    'availableFlairs' => $availableFlairs,
                    'selectedFlairIds' => $selectedFlairIds,
                    'feedUrl' => route('communities.feed', $community),
                ])
            </div>


            @if ($canModerate && $community->isProgramBatch())
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h4 class="text-lg font-semibold text-gray-900">{{ __('Pending join requests') }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Decide who joins. If a requestor is already a member of another program-batch community, you must wait for them to leave it before accepting.') }}</p>
                    </div>
                    @if ($pendingJoinRequests->isEmpty())
                        <p class="px-6 py-6 text-center text-sm text-gray-500">{{ __('No pending requests.') }}</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach ($pendingJoinRequests as $jr)
                                @php($otherPb = $jr->user?->programBatchCommunity())
                                <li class="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $jr->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $jr->created_at->diffForHumans() }}</p>
                                        @if ($otherPb && $otherPb->id !== $community->id)
                                            <p class="mt-1 text-xs font-medium text-amber-800">
                                                {{ __('Already in ":n" — must leave before accepting.', ['n' => $otherPb->name]) }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('communities.join-requests.accept', [$community, $jr]) }}">
                                            @csrf
                                            <button type="submit"
                                                @disabled($otherPb && $otherPb->id !== $community->id)
                                                class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                                {{ __('Accept') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('communities.join-requests.ignore', [$community, $jr]) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center rounded-md border border-rose-300 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                                {{ __('Ignore') }}
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-5">
                        <h4 class="text-lg font-semibold text-gray-900">{{ __('Manage members') }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Remove a member from this community. Other moderators cannot be removed.') }}</p>
                    </div>
                    @if ($community->members->isEmpty())
                        <p class="px-6 py-6 text-center text-sm text-gray-500">{{ __('No members yet.') }}</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach ($community->members as $member)
                                @if ($member->id === $user->id)
                                    @continue
                                @endif
                                <li class="flex items-center justify-between px-6 py-3">
                                    <a href="{{ route('profiles.show', $member) }}" class="text-sm font-medium text-gray-900 hover:underline">
                                        {{ $member->name }}
                                    </a>
                                    @if ($community->isModerator($member) && ! $isAdmin)
                                        <span class="text-xs font-semibold uppercase tracking-wide text-indigo-700">{{ __('Moderator') }}</span>
                                    @else
                                        <form method="POST" action="{{ route('communities.members.remove', [$community, $member]) }}">
                                            @csrf
                                            @method('delete')
                                            <button type="submit"
                                                onclick="return confirm('{{ __('Remove this member?') }}')"
                                                class="inline-flex items-center rounded-md border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                                {{ __('Remove') }}
                                            </button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            {{-- Members modal (opened from the Members stat card) --}}
            <div x-show="open" x-cloak @keydown.escape.window="close()"
                class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4" style="display:none;">
                <div class="fixed inset-0 bg-black/50" @click="close()"></div>
                <div class="relative z-10 flex max-h-[85vh] sm:max-h-[80vh] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl sm:rounded-2xl border border-gray-200 bg-white shadow-2xl">
                    <div class="flex shrink-0 items-center justify-between border-b border-gray-100 px-5 py-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ __('Members') }}</h3>
                            <p class="text-xs text-gray-500">
                                <span x-text="count ?? {{ $community->members_count }}"></span>
                                {{ __('in') }} {{ $community->name }}
                            </p>
                        </div>
                        <button type="button" @click="close()"
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500 text-lg leading-none hover:bg-gray-200 transition" aria-label="{{ __('Close') }}">&times;</button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-5">
                        @if ($isVerified)
                            {{-- Loading --}}
                            <div x-show="loading" class="flex justify-center py-10 text-gray-400">
                                <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                            </div>
                            <div x-show="error" x-text="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" style="display:none;"></div>
                            <div x-show="loaded && count === 0" class="rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500" style="display:none;">
                                {{ __('No members yet.') }}
                            </div>
                            <div x-show="loaded && count > 0" class="grid gap-2 sm:grid-cols-2" x-html="html" style="display:none;"></div>
                        @else
                            <div class="flex flex-col items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-6 py-8 text-center">
                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <h5 class="text-sm font-semibold text-amber-900">{{ __('Verify your account to view members') }}</h5>
                                <p class="max-w-md text-sm text-amber-800">{{ __('Member profiles are hidden until your alumni status is verified.') }}</p>
                                @if ($user->hasPendingVerificationDocument())
                                    <span class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg bg-amber-200 px-4 py-2 text-sm font-semibold text-amber-500">{{ __('Pending review') }}</span>
                                @else
                                    <a href="{{ route('profile.edit', ['section' => 'verification-document']) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">{{ __('Verify now') }}</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-post-detail-modal />

    @include('partials.feed-scripts')

    <script>
        function communityMembersModal(url, isVerified) {
            return {
                url,
                isVerified,
                open: false,
                loading: false,
                loaded: false,
                error: null,
                html: '',
                count: null,

                openMembers() {
                    this.open = true;
                    document.body.classList.add('overflow-hidden');
                    if (this.isVerified && !this.loaded && !this.loading) this.fetchMembers();
                },
                close() {
                    this.open = false;
                    document.body.classList.remove('overflow-hidden');
                },
                fetchMembers() {
                    this.loading = true;
                    this.error = null;
                    fetch(this.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => { if (!r.ok) throw new Error('Failed to load members'); return r.json(); })
                        .then(d => {
                            this.html = d.html;
                            this.count = d.count;
                            this.loaded = true;
                            this.loading = false;
                        })
                        .catch(e => { this.error = e.message || 'Error loading members'; this.loading = false; });
                },
            };
        }
    </script>
</x-app-layout>