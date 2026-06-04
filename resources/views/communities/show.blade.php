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
                        <div
                            x-data="{
                                editing: false,
                                text: @js($community->description ?? ''),
                            }"
                            class="min-w-0 flex-1">
                            <h3 class="text-2xl font-semibold text-gray-900">{{ $community->name }}</h3>

                            {{-- Display mode --}}
                            <div x-show="!editing" class="group mt-2 flex items-start gap-2">
                                <p class="min-w-0 break-all text-sm text-gray-600" x-text="text || '{{ __('No description provided yet.') }}'"></p>
                                @if ($canModerate)
                                    <button type="button" @click="editing = true"
                                        class="mt-0.5 shrink-0 text-gray-400 transition hover:text-gray-600"
                                        aria-label="{{ __('Edit description') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            {{-- Edit mode --}}
                            @if ($canModerate)
                                <form x-show="editing" method="POST"
                                    action="{{ route('communities.description.update', $community) }}"
                                    class="mt-2 space-y-2">
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
                                                @if($otherPb && $otherPb->id !== $community->id) disabled @endif
                                                class="inline-flex items-center justify-center rounded-md bg-red-900 px-3 py-2 text-xs font-semibold tracking-widest text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                                {{ __('Accept') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('communities.join-requests.ignore', [$community, $jr]) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold tracking-widest text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
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
                                        <div x-data="{
                                                open: false,
                                                above: false,
                                                toggle() {
                                                    const rect = this.$el.getBoundingClientRect();
                                                    this.above = rect.bottom + 100 > window.innerHeight;
                                                    this.open = !this.open;
                                                }
                                            }" class="relative">
                                            <button type="button" @click="toggle()" @keydown.escape.window="open = false"
                                                class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 6a2 2 0 1 1 0-4 2 2 0 0 1 0 4ZM10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4ZM10 18a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z"/>
                                                </svg>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-transition
                                                :class="above ? 'bottom-full mb-1' : 'top-full mt-1'"
                                                class="absolute right-0 z-20 w-44 rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
                                                @if ($isModerator && ! $isAdmin)
                                                    @if (isset($myPendingTransfers[$member->id]))
                                                        @php($pendingTransfer = $myPendingTransfers[$member->id])
                                                        <div class="flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-indigo-600">
                                                            <svg class="h-3.5 w-3.5 shrink-0 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/>
                                                            </svg>
                                                            {{ __('Transfer pending…') }}
                                                        </div>
                                                        <form method="POST" action="{{ route('mod-transfers.cancel', $pendingTransfer) }}">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="submit"
                                                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-gray-600 hover:bg-gray-50">
                                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                                                </svg>
                                                                {{ __('Cancel Invite') }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST" action="{{ route('communities.mod-transfer.store', [$community, $member]) }}">
                                                            @csrf
                                                            <button type="submit"
                                                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-indigo-700 hover:bg-indigo-50">
                                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                                                </svg>
                                                                {{ __('Transfer Role') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                                <form method="POST" action="{{ route('communities.members.remove', [$community, $member]) }}">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit"
                                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-rose-700 hover:bg-rose-50">
                                                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>
                                                        </svg>
                                                        {{ __('Remove') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
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