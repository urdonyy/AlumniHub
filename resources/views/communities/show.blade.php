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
                <div class="space-y-5 px-6 py-6"
                    x-data="{
                        editing: false,
                        text: @js($community->description ?? ''),
                    }">
                    <div class="flex flex-col gap-1.5">
                        <h3 class="text-2xl font-semibold text-gray-900 sm:text-3xl">{{ $community->name }}</h3>

                        {{-- Description display --}}
                        <div x-show="!editing">
                            <p class="min-w-0 break-all text-sm text-gray-600 sm:text-base" x-text="text || '{{ __('No description provided yet.') }}'"></p>
                            @if ($canModerate)
                                <div class="mt-1.5 flex justify-end">
                                    <button type="button" @click="editing = true"
                                        class="text-red-900/60 transition hover:text-red-900"
                                        aria-label="{{ __('Edit description') }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Description edit form --}}
                        @if ($canModerate)
                            <form x-show="editing" method="POST"
                                action="{{ route('communities.description.update', $community) }}"
                                class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <textarea name="description" rows="3" required minlength="10" maxlength="2000"
                                    x-model="text"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-red-300 focus:outline-none focus:ring-1 focus:ring-red-50 resize-none"></textarea>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="inline-flex items-center rounded-md bg-red-900 px-3 py-1.5 text-xs font-semibold tracking-widest text-white transition hover:bg-red-800">
                                        {{ __('Save') }}
                                    </button>
                                    <button type="button" @click="editing = false"
                                        class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-50">
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                            </form>
                        @endif

                        {{-- Badge row: access badge left, mod buttons right --}}
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $user->communityAccessBadgeClass() }}">
                                {{ $user->communityAccessLabel() }}
                            </span>

                            @if ($canModerate && $community->isProgramBatch())
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('communities.join-requests.index', $community) }}"
                                        class="relative inline-flex items-center gap-1.5 rounded-md border border-red-900/40 px-3 py-1 text-xs font-semibold text-red-900 transition hover:bg-red-50">
                                        <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                                        <span class="hidden sm:inline">{{ __('Join Requests') }}</span>
                                        @if ($pendingJoinRequestCount > 0)
                                            <span class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white">{{ $pendingJoinRequestCount }}</span>
                                        @endif
                                    </a>
                                    <a href="{{ route('communities.manage-members', $community) }}"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-red-900/40 px-3 py-1 text-xs font-semibold text-red-900 transition hover:bg-red-50">
                                        <i class="fa-solid fa-users-gear" aria-hidden="true"></i>
                                        <span class="hidden sm:inline">{{ __('Manage Members') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1 sm:gap-0 sm:rounded-lg sm:border sm:border-gray-200 sm:bg-gray-50">
                        {{-- Members --}}
                        <button type="button" @click="openMembers()"
                            class="relative flex flex-col items-center justify-center gap-[6px] p-0 sm:p-4 transition hover:bg-gray-100 focus:outline-none min-w-0">
                            <div class="flex items-center justify-center gap-1">
                                <i class="fa-solid fa-users text-red-900 text-xs sm:text-sm lg:text-xl"></i>
                                <p class="text-sm sm:text-lg lg:text-2xl font-semibold text-red-900">{{ $community->members_count }}</p>
                            </div>
                            <p class="min-w-0 truncate text-xs lg:text-base font-semibold uppercase tracking-wide text-[#FFC107]">{{ __('Members') }}</p>
                            <div class="border border-red-900 absolute h-[50%] right-0 hidden sm:flex flex-col items-center"></div>
                        </button>

                        {{-- Posts --}}
                        <a href="#activity"
                            class="relative flex flex-col items-center justify-center gap-[6px] p-0 sm:p-4 min-w-0 border-l border-red-900/30 sm:border-l-0 transition hover:bg-gray-100">
                            <div class="flex items-center justify-center gap-1">
                                <i class="fa-solid fa-pen-to-square text-red-900 text-xs sm:text-sm lg:text-xl"></i>
                                <p class="text-sm sm:text-lg lg:text-2xl font-semibold text-red-900">{{ $postCount }}</p>
                            </div>
                            <p class="min-w-0 truncate text-xs lg:text-base font-semibold uppercase tracking-wide text-[#FFC107]">{{ $postCount !== 1 ? __('Posts') : __('Post') }}</p>
                            <div class="border border-red-900 absolute h-[50%] right-0 hidden sm:flex flex-col items-center"></div>
                        </a>

                        {{-- Membership status --}}
                        <div class="col-span-2 sm:col-span-1 flex flex-col items-center justify-center gap-[6px] p-0 pt-2 sm:p-4 min-w-0 border-t border-red-900/30 sm:border-t-0">
                            @if ($isMember)
                                <i class="fa-solid fa-circle-check text-red-900 text-xs sm:text-sm lg:text-xl"></i>
                                <p class="min-w-0 truncate text-xs lg:text-base font-semibold uppercase tracking-wide text-[#FFC107]">{{ __('Joined') }}</p>
                            @elseif ($community->is_system)
                                <i class="fa-solid fa-ban text-red-900 text-xs sm:text-sm lg:text-xl"></i>
                                <p class="min-w-0 truncate text-xs lg:text-base font-semibold uppercase tracking-wide text-[#FFC107]">{{ __('Not Eligible') }}</p>
                            @else
                                <i class="fa-solid fa-circle-xmark text-red-900 text-xs sm:text-sm lg:text-xl"></i>
                                <p class="min-w-0 truncate text-xs lg:text-base font-semibold uppercase tracking-wide text-[#FFC107]">{{ __('Not Joined') }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($community->rules->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($community->rules as $rule)
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ $rule->batch_year ? 'Batch ' . $rule->batch_year : __('Any batch') }}
                                    ·
                                    {{ $rule->program_course ?? __('Any program') }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Incoming transfer invite --}}
                    @if ($pendingTransferToMe ?? null)
                        <div class="flex flex-col gap-3 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-indigo-900">{{ __('Moderator role offer') }}</p>
                                <p class="mt-0.5 text-xs text-indigo-700">
                                    {{ $pendingTransferToMe->fromUser->name }} {{ __('is offering you the moderator role in this community.') }}
                                </p>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <form method="POST" action="{{ route('mod-transfers.accept', $pendingTransferToMe) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-600">
                                        {{ __('Accept') }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('mod-transfers.decline', $pendingTransferToMe) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-md border border-indigo-300 px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                        {{ __('Decline') }}
                                    </button>
                                </form>
                            </div>
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
                                @if ($community->isProgramBatch() && $isModerator)
                                    <div class="flex items-center gap-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                        </svg>
                                        {{ __('Moderators cannot leave. Transfer your role to another member first.') }}
                                    </div>
                                @else
                                    <form method="post" action="{{ route('communities.leave', $community) }}">
                                        @csrf
                                        @method('delete')
                                        <x-primary-button>{{ __('Leave Community') }}</x-primary-button>
                                    </form>
                                @endif
                            @elseif ($community->isProgramBatch())
                                @if ($pendingJoinRequest)
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center rounded-md bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-800">
                                            {{ __('Join request pending') }}
                                        </span>
                                        <form method="post" action="{{ route('communities.join-requests.withdraw', [$community, $pendingJoinRequest]) }}">
                                            @csrf
                                            @method('delete')
                                            <button type="submit"
                                                class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-50">
                                                {{ __('Withdraw') }}
                                            </button>
                                        </form>
                                    </div>
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
            <div id="activity" class="space-y-3">
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

    {{-- Toast notifications --}}
    @if (session('status') === 'join-request-submitted')
        <x-toast message="{{ __('Your join request was submitted. Moderators will be notified.') }}" color="emerald" />
    @elseif (session('status') === 'join-request-withdrawn')
        <x-toast message="{{ __('Your join request was withdrawn.') }}" />
    @elseif (session('status') === 'join-request-already-accepted')
        <x-toast message="{{ __('A moderator already accepted your request before you withdrew — you are now a member!') }}" color="emerald" />
    @elseif (session('status') === 'co-mod-cannot-leave')
        <x-toast message="{{ __('Moderators cannot leave. Transfer your role to another member first.') }}" color="rose" />
    @elseif (session('status') === 'already-a-member')
        <x-toast message="{{ __('You are already a member of this community.') }}" color="blue" />
    @elseif (session('status') === 'member-removed')
        <x-toast message="{{ __('Member removed.') }}" color="emerald" />
    @elseif (session('status') === 'transfer-invite-sent')
        <x-toast message="{{ __('Transfer invite sent. The member must accept before you can leave.') }}" color="indigo" />
    @elseif (session('status') === 'transfer-accepted')
        <x-toast message="{{ __('You accepted the moderator role.') }}" color="emerald" />
    @elseif (session('status') === 'transfer-declined')
        <x-toast message="{{ __('You declined the moderator role transfer.') }}" />
    @elseif (session('status') === 'transfer-cancelled')
        <x-toast message="{{ __('Transfer invite cancelled.') }}" />
    @elseif (session('status') === 'description-updated')
        <x-toast message="{{ __('Description updated.') }}" color="emerald" />
    @elseif ($errors->has('member'))
        <x-toast message="{{ $errors->first('member') }}" color="rose" />
    @elseif ($errors->has('join_request'))
        <x-toast message="{{ $errors->first('join_request') }}" color="rose" />
    @endif

    <x-post-detail-modal />
    <x-report-post-modal />
    <x-remove-post-modal />

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