<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $community->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
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
                            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $user->communityAccessBadgeClass() }}">
                            {{ $user->communityAccessLabel() }}
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Members') }}
                            </p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $community->members_count }}</p>
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
                                {{ $isMember ? __('Joined') : __('Not joined') }}
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
                        @if ($isVerified)
                            <a href="{{ route('communities.posts.index', $community) }}"
                                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                📝 {{ __('View Posts') }}
                            </a>
                        @endif

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

                        <a href="{{ route('communities.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            {{ __('Back to communities') }}
                        </a>

                        @if ($user->canManageCommunities())
                            <a href="{{ route('admin.communities.index') }}"
                                class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-600">
                                {{ __('Manage Communities') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="px-6 py-5">
                    <h4 class="text-lg font-semibold text-gray-900">{{ __('Members') }}</h4>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Browse member profiles. Unverified viewers will only see limited details.') }}
                    </p>
                </div>

                @if ($isVerified)
                    <div class="border-t border-gray-200 px-6 py-5 text-sm text-gray-600 space-y-3">
                        <p>{{ __('Member count: :count', ['count' => $community->members_count]) }}</p>

                        @if ($community->members->isNotEmpty())
                            <ul class="grid gap-2 sm:grid-cols-2">
                                @foreach ($community->members as $member)
                                    <li>
                                        <a href="{{ route('profiles.show', $member) }}"
                                            class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            {{ $member->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @else
                    <div class="border-t border-gray-200 px-6 py-8">
                        <div class="flex flex-col items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-6 py-8 text-center">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <h5 class="text-sm font-semibold text-amber-900">{{ __('Verify your account to view members and posts') }}</h5>
                            <p class="max-w-md text-sm text-amber-800">
                                {{ __('Community posts and member profiles are hidden until your alumni status is verified. Complete verification to unlock the full community.') }}
                            </p>
                            @if ($user->hasPendingVerificationDocument())
                                <div class="relative group mt-1 inline-block">
                                    <span class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg bg-amber-200 px-4 py-2 text-sm font-semibold text-amber-500">
                                        {{ __('Pending review') }}
                                    </span>
                                    <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 px-2.5 py-1.5 text-xs text-white group-hover:block">
                                        {{ __('Waiting for admin approval') }}
                                    </div>
                                </div>
                            @else
                                <a href="{{ route('profile.edit', ['section' => 'verification-document']) }}"
                                    class="mt-1 inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">
                                    {{ __('Verify now') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
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
        </div>
    </div>
</x-app-layout>