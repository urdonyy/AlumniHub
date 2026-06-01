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

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 px-6 py-5">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900">{{ __('Activity') }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Recent posts in this community.') }}</p>
                    </div>
                    <span class="shrink-0 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">
                        {{ $activityPosts->count() }}
                    </span>
                </div>

                <div class="border-t border-gray-200 px-6 py-5">
                    @if ($activityPosts->isEmpty())
                        <div class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-600">
                            {{ __('No posts to show yet.') }}
                        </div>
                    @else
                        @php
                            $activityPostsCounts = $activityPosts->map(fn ($p) => [
                                'id' => $p->id,
                                'like_count' => (int) ($p->likes_count ?? $p->like_count),
                                'comment_count' => (int) ($p->comments_count ?? $p->comment_count),
                            ])->values();
                        @endphp

                        <div x-data="communityPostsCarousel(@js($activityPostsCounts))">
                            <div class="relative">
                                <div class="overflow-hidden" style="touch-action: pan-y;">
                                    <div class="flex transition-transform duration-300 ease-out js-community-posts-track"
                                        :style="`transform: translateX(-${page * 100}%);`">
                                        @foreach ($activityPosts as $post)
                                            @php
                                                $visibilityConfig = match ($post->visibility) {
                                                    'public'      => ['bg-green-50 text-green-700 ring-green-200', __('Public')],
                                                    'connections' => ['bg-blue-50 text-blue-700 ring-blue-200', __('Connections')],
                                                    default       => ['bg-gray-100 text-gray-600 ring-gray-200', __('Members')],
                                                };
                                            @endphp
                                            <div class="shrink-0 px-1.5" :style="`width: ${100 / perView}%`">
                                                <article
                                                    class="flex h-full cursor-pointer flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-gray-300 hover:shadow-md"
                                                    @click="openPostModal($event, {{ $post->id }}, '{{ route('communities.posts.api', ['community' => $post->community, 'post' => $post]) }}', '{{ route('communities.posts.comments.index', ['community' => $post->community, 'post' => $post]) }}')">

                                                    <div class="p-4 pb-3">
                                                        <div class="flex items-start justify-between gap-3">
                                                            <div class="flex min-w-0 items-center gap-3">
                                                                <img src="{{ $post->user->profileAvatarUrl() }}"
                                                                    alt="{{ $post->user->name }}"
                                                                    class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover"
                                                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                                                                <div class="min-w-0">
                                                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $post->user->name }}</p>
                                                                    <p class="truncate text-xs text-gray-500">
                                                                        {{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $visibilityConfig[0] }}">
                                                                {{ $visibilityConfig[1] }}
                                                            </span>
                                                        </div>

                                                        @if ($post->flairs->count() > 0)
                                                            <div class="mt-3 flex flex-wrap gap-1.5">
                                                                @foreach ($post->flairs->take(4) as $flair)
                                                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                                        style="background-color: {{ $flair->color ? $flair->color . '20' : '#f3f4f6' }}; color: {{ $flair->color ?? '#374151' }}; border: 1px solid {{ $flair->color ?? '#e5e7eb' }};">
                                                                        @if ($flair->icon)
                                                                            <span>{{ $flair->icon }}</span>
                                                                        @endif
                                                                        {{ $flair->name }}
                                                                    </span>
                                                                @endforeach
                                                                @if ($post->flairs->count() > 4)
                                                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                                                                        +{{ $post->flairs->count() - 4 }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="flex flex-1 flex-col px-4 pb-4">
                                                        @if ($post->title)
                                                            <h4 class="line-clamp-2 text-base font-semibold text-gray-900">{{ $post->title }}</h4>
                                                        @endif

                                                        <p class="mt-2 line-clamp-3 break-words text-sm leading-6 text-gray-700">
                                                            {{ \Illuminate\Support\Str::limit(strip_tags($post->body_html ?? $post->body_markdown), 180) }}
                                                        </p>

                                                        @if ($post->media->count() > 0)
                                                            <div class="mt-4 grid grid-cols-3 gap-2">
                                                                @foreach ($post->media->take(3) as $idx => $media)
                                                                    <div class="relative aspect-[4/3] max-h-40 overflow-hidden rounded-lg bg-gray-100">
                                                                        <img src="{{ $media->url }}" alt="{{ __('Post image') }}"
                                                                            class="h-full w-full max-h-40 object-cover" />
                                                                        @if ($idx === 2 && $post->media->count() > 3)
                                                                            <div class="absolute inset-0 flex items-center justify-center bg-black/55 text-sm font-semibold text-white">
                                                                                +{{ $post->media->count() - 3 }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                        <div class="mt-4 flex flex-wrap items-center gap-2 pt-1">
                                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                                </svg>
                                                                <span x-text="getLikeCount({{ $post->id }}, {{ (int) ($post->likes_count ?? $post->like_count) }})"></span>
                                                            </span>
                                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2l-4 4z" />
                                                                </svg>
                                                                <span x-text="getCommentCount({{ $post->id }}, {{ (int) ($post->comments_count ?? $post->comment_count) }})"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </article>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <template x-if="totalPages > 1">
                                    <div>
                                        <button type="button" @click="prev()" :disabled="page === 0"
                                            class="absolute left-1 top-1/2 -translate-y-1/2 rounded-full bg-gray-900/10 px-3 py-2 text-gray-900 transition hover:bg-gray-900/20 disabled:opacity-30"
                                            aria-label="{{ __('Previous') }}">
                                            ‹
                                        </button>
                                        <button type="button" @click="next()" :disabled="page >= totalPages - 1"
                                            class="absolute right-1 top-1/2 -translate-y-1/2 rounded-full bg-gray-900/10 px-3 py-2 text-gray-900 transition hover:bg-gray-900/20 disabled:opacity-30"
                                            aria-label="{{ __('Next') }}">
                                            ›
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <template x-if="totalPages > 1">
                                <div class="mt-4 flex items-center justify-center gap-1 text-xs font-medium text-gray-500">
                                    <span x-text="page + 1"></span>
                                    <span>/</span>
                                    <span x-text="totalPages"></span>
                                </div>
                            </template>
                        </div>
                    @endif
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
                    <div class="border-t border-gray-200 px-6 py-5">
                        <p class="text-sm text-gray-600">{{ __('Member count: :count', ['count' => $community->members_count]) }}</p>

                        @if ($community->members->isNotEmpty())
                            <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($community->members as $member)
                                    <li>
                                        <a href="{{ route('profiles.show', $member) }}"
                                            class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 transition hover:border-gray-300 hover:bg-gray-50">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-50 text-xs font-semibold uppercase text-red-900">
                                                {{ \Illuminate\Support\Str::substr($member->name, 0, 1) }}
                                            </span>
                                            <span class="truncate text-sm font-medium text-gray-700">{{ $member->name }}</span>
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

    <x-post-detail-modal />

    <script>
        function communityPostsCarousel(initialPosts) {
            const items = Array.isArray(initialPosts) ? initialPosts : [];
            const initialById = {};
            for (const item of items) {
                if (!item || typeof item.id !== 'number') continue;
                initialById[item.id] = {
                    like_count: Number(item.like_count) || 0,
                    comment_count: Number(item.comment_count) || 0,
                };
            }

            return {
                total: items.length,
                perView: 3,
                page: 0,
                countsByPostId: initialById,

                get totalPages() {
                    return Math.max(1, Math.ceil(this.total / this.perView));
                },

                init() {
                    this.updatePerView();
                    window.addEventListener('resize', () => this.updatePerView());

                    window.addEventListener('post-like-count-changed', (event) => {
                        const postId = Number(event?.detail?.postId);
                        const count = Number(event?.detail?.count);
                        if (!Number.isFinite(postId) || !Number.isFinite(count)) return;
                        if (!this.countsByPostId[postId]) this.countsByPostId[postId] = { like_count: 0, comment_count: 0 };
                        this.countsByPostId[postId].like_count = count;
                    });

                    window.addEventListener('post-comment-count-changed', (event) => {
                        const postId = Number(event?.detail?.postId);
                        const count = Number(event?.detail?.count);
                        if (!Number.isFinite(postId) || !Number.isFinite(count)) return;
                        if (!this.countsByPostId[postId]) this.countsByPostId[postId] = { like_count: 0, comment_count: 0 };
                        this.countsByPostId[postId].comment_count = count;
                    });
                },

                updatePerView() {
                    const w = window.innerWidth;
                    this.perView = w >= 1024 ? 3 : (w >= 640 ? 2 : 1);
                    if (this.page > this.totalPages - 1) {
                        this.page = Math.max(0, this.totalPages - 1);
                    }
                },

                next() {
                    if (this.page < this.totalPages - 1) this.page++;
                },

                prev() {
                    if (this.page > 0) this.page--;
                },

                getLikeCount(postId, fallback = 0) {
                    const entry = this.countsByPostId?.[postId];
                    return typeof entry?.like_count === 'number' ? entry.like_count : fallback;
                },

                getCommentCount(postId, fallback = 0) {
                    const entry = this.countsByPostId?.[postId];
                    return typeof entry?.comment_count === 'number' ? entry.comment_count : fallback;
                },

                openPostModal(event, postId, apiUrl, commentsUrl) {
                    event?.preventDefault?.();
                    window.dispatchEvent(new CustomEvent('post-modal-opened', {
                        detail: { postId, apiUrl, commentsUrl }
                    }));
                },
            };
        }
    </script>
</x-app-layout>