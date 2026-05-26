<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                {{ auth()->user()->canManageCommunities() ? __('Admin Home') : __('Home') }}
            </h2>
            <p class="text-sm text-red-900">{{ __('AlumniHub social experience (beta)') }}</p>
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

                <div class="grid gap-6 lg:grid-cols-12">
                    <aside class="space-y-3 lg:col-span-3">
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
                                <a class="block text-gray-700 hover:text-gray-900" href="{{ route('connections.index') }}">{{ __('Connections') }}</a>
                                <a class="block text-gray-700 hover:text-gray-900" href="{{ route('saved.index') }}">{{ __('Saved') }}</a>
                                <a class="block text-gray-700 hover:text-gray-900" href="{{ route('profile.edit', ['section' => 'account-status']) }}">{{ __('Account Settings') }}</a>
                            </div>
                        </section>
                    </aside>

                    <section class="space-y-6 lg:col-span-6" x-data="feedManager()" @feedManager-openPostModal.window="openPostModal($event, $event.detail.postId, $event.detail.apiUrl, $event.detail.commentsUrl)">
                        @php
                            /** @var \Illuminate\Support\Collection<int, \App\Models\Community> $joinedCommunitiesCollection */
                            $joinedCommunitiesCollection = collect($joinedCommunities ?? []);
                            $defaultCommunityId = old('community_id')
                                ?? ($joinedCommunitiesCollection->firstWhere('system_key', 'general-alumni-hub')->id ?? null);
                        @endphp

                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm space-y-3"
                            x-data="postComposer(@js($flairsByCommunity ?? []), {{ $defaultCommunityId ?? 'null' }})">
                            <button type="button"
                                @click="open = true"
                                class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-left text-gray-500 transition hover:bg-gray-100">
                                What's on your mind, {{ auth()->user()->name }}?
                            </button>

                            <div x-show="open"
                                x-transition.opacity
                                @keydown.escape.window="open = false"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                style="display: none;">
                                <div @click.away="open = false"
                                    class="w-full max-w-2xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">
                                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                        <h3 class="text-lg font-semibold text-gray-900">Create post</h3>
                                        <button type="button" @click="open = false"
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-xl leading-none text-gray-600 hover:bg-gray-200">
                                            ×
                                        </button>
                                    </div>

                                    <form method="post" action="{{ route('posts.quick-store') }}"
                                        enctype="multipart/form-data" class="max-h-[80vh] overflow-y-auto space-y-4 px-5 py-5">
                                        @csrf

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700">Community</label>
                                            <select name="community_id" required
                                                x-model="communityId"
                                                class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 shadow-sm focus:border-red-900 focus:ring-red-900">
                                                <option value="">Select community</option>
                                                @foreach ($joinedCommunitiesCollection as $joinedCommunity)
                                                    <option value="{{ $joinedCommunity->id }}"
                                                        @selected($defaultCommunityId == $joinedCommunity->id)>
                                                        {{ $joinedCommunity->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <input type="text" name="title"
                                                class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-red-900 focus:ring-red-900"
                                                placeholder="Post title (optional)">
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700">Audience</label>
                                            <select name="visibility"
                                                class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 shadow-sm focus:border-red-900 focus:ring-red-900">
                                                <option value="members" @selected(old('visibility', 'members') === 'members')>Community members only</option>
                                                <option value="connections" @selected(old('visibility') === 'connections')>Connections only</option>
                                                <option value="public" @selected(old('visibility') === 'public')>Public</option>
                                            </select>
                                        </div>

                                        <div>
                                            <textarea name="body_markdown" rows="6" required
                                                class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-red-900 focus:ring-red-900"
                                                placeholder="What's on your mind, {{ auth()->user()->name }}?"></textarea>
                                        </div>

                                        <template x-if="filteredFlairs.length > 0">
                                            <div>
                                                <label class="mb-2 block text-sm font-medium text-gray-700">Flair tag</label>
                                                <div class="grid gap-1.5 sm:grid-cols-2 lg:grid-cols-3">
                                                    <template x-for="flair in filteredFlairs" :key="flair.id">
                                                        <label class="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 px-3 py-2 hover:bg-gray-50">
                                                            <input type="checkbox" name="flairs[]" :value="flair.id"
                                                                class="h-3.5 w-3.5 rounded border-gray-300 text-red-900 focus:ring-red-900" />
                                                            <div class="flex flex-1 items-center gap-1.5 min-w-0">
                                                                <span x-show="flair.icon" x-text="flair.icon" class="text-xs leading-none"></span>
                                                                <span x-text="flair.name" class="truncate text-xs font-medium text-gray-900"></span>
                                                                <span x-show="flair.color"
                                                                    class="ml-auto inline-block h-2 w-2 shrink-0 rounded-full"
                                                                    :style="`background-color: ${flair.color}`"></span>
                                                            </div>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700">Add images</label>
                                            <input type="file" name="attachments[]" multiple accept="image/*"
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-red-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-red-800">
                                        </div>

                                        <button type="submit"
                                            class="w-full rounded-lg bg-red-900 px-4 py-3 font-semibold text-white hover:bg-red-800 transition">
                                            Post
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>{{-- end composer card --}}

                        @if(isset($posts))
                            @foreach($posts as $post)
                                <article x-data="postCard({{ $post->id }}, {{ $post->like_count }}, {{ $post->comments_count ?? 0 }}, '{{ route('communities.posts.api', ['community' => $post->community, 'post' => $post]) }}', '{{ route('communities.posts.like', ['community' => $post->community, 'post' => $post]) }}', {{ $post->isLikedByAuthUser() ? 'true' : 'false' }})"
                                    @click="openPostModal($event)"
                                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm cursor-pointer transition hover:shadow-md hover:border-gray-300">

                                    <!-- Post Header -->
                                    <div class="p-4 pb-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $post->user->profileAvatarUrl() }}"
                                                    alt="{{ $post->user->name }}"
                                                    class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover"
                                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900">{{ $post->user->name }}</p>
                                                    @php
                                                        $batchLabel = $post->user->batch_year ? 'Batch ' . $post->user->batch_year : null;
                                                        $programAbbr = null;
                                                        if ($post->user->program_course && preg_match('/\(([^)]+)\)$/', $post->user->program_course, $m)) {
                                                            $programAbbr = $m[1];
                                                        }
                                                    @endphp
                                                    <p class="text-xs text-gray-500">
                                                        @if($batchLabel || $programAbbr)
                                                            {{ implode(' · ', array_filter([$batchLabel, $programAbbr])) }}
                                                            <span class="mx-1">·</span>
                                                        @endif
                                                        {{ $post->community?->name ?? __('Post') }}
                                                        @if($post->published_at)
                                                            · {{ $post->published_at->diffForHumans() }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            @php
                                                $visibilityConfig = match($post->visibility) {
                                                    'public'      => ['bg-green-50 text-green-700 ring-green-200', 'Public'],
                                                    'connections' => ['bg-blue-50 text-blue-700 ring-blue-200', 'Connections'],
                                                    default       => ['bg-gray-100 text-gray-600 ring-gray-200', 'Members'],
                                                };
                                            @endphp
                                            <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $visibilityConfig[0] }}">
                                                {{ $visibilityConfig[1] }}
                                            </span>
                                        </div>

                                        @if($post->flairs->count() > 0)
                                            <div class="mt-3 flex flex-wrap gap-1.5">
                                                @foreach($post->flairs as $flair)
                                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                        style="background-color: {{ $flair->color ? $flair->color . '20' : '#f3f4f6' }}; color: {{ $flair->color ?? '#374151' }}; border: 1px solid {{ $flair->color ?? '#e5e7eb' }};">
                                                        @if($flair->icon)<span>{{ $flair->icon }}</span>@endif
                                                        {{ $flair->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($post->title)
                                            <h4 class="mt-2 text-base font-semibold text-gray-900">{{ $post->title }}</h4>
                                        @endif
                                        <p class="mt-2 text-sm leading-6 text-gray-700 line-clamp-3">{{ \Illuminate\Support\Str::limit(strip_tags($post->body_html ?? $post->body_markdown), 200) }}</p>
                                    </div>

                                    <!-- Post Media -->
                                    @if($post->media->count() > 0)
                                        <div class="relative overflow-hidden bg-gray-50">
                                            <img src="/storage/{{ $post->media->first()->file_path }}"
                                                alt="Post image"
                                                class="w-full max-h-80 object-contain">
                                            @if($post->media->count() > 1)
                                                <div class="absolute bottom-2 right-2 rounded-full bg-black/60 px-2.5 py-1 text-xs font-medium text-white">
                                                    +{{ $post->media->count() - 1 }} more
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Post Footer -->
                                    <div class="border-t border-gray-100 px-2 py-1">
                                        <div class="flex">
                                            <button type="button" @click.stop="toggleLike()"
                                                :disabled="isLikingLoading"
                                                :class="{ 'text-red-700': isLiked, 'text-gray-600': !isLiked, 'opacity-60': isLikingLoading }"
                                                class="flex flex-1 items-center justify-center gap-2 rounded-lg py-2 text-sm font-medium transition hover:bg-gray-50">
                                                <svg class="h-4 w-4" :fill="isLiked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                <span x-text="likeCount + (likeCount === 1 ? ' Like' : ' Likes')"></span>
                                            </button>
                                            <button type="button" @click.stop="openPostModal($event)"
                                                class="flex flex-1 items-center justify-center gap-2 rounded-lg py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2l-4 4z" />
                                                </svg>
                                                <span x-text="commentCount + (commentCount === 1 ? ' Comment' : ' Comments')"></span>
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach

                            <div class="pt-4">{{ $posts->links() }}</div>
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

                        <x-post-detail-modal />
                    </section>

                    <aside class="space-y-3 lg:col-span-3">
                        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Batch Communities') }}</h3>
                                <a href="{{ route('communities.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-700">
                                    {{ __('See all') }}
                                </a>
                            </div>

                            <div class="mt-4 space-y-3">
                                @forelse ($featuredCommunities as $community)
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
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                                {{ __('Suggested People') }}</h3>
                            <div class="mt-4 space-y-3">
                                @forelse ($suggestedPeople as $person)
                                    <a href="{{ route('profiles.show', $person) }}"
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
                communityId: defaultCommunityId ? String(defaultCommunityId) : '',
                flairsByCommunity,

                get filteredFlairs() {
                    const global = this.flairsByCommunity['global'] || [];
                    const community = this.communityId
                        ? (this.flairsByCommunity[String(this.communityId)] || [])
                        : [];
                    const seen = new Set();
                    return [...global, ...community].filter(f => {
                        if (seen.has(f.id)) return false;
                        seen.add(f.id);
                        return true;
                    });
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

        function postCard(postId, initialLikeCount, initialCommentCount, apiUrl, likeUrl, isInitiallyLiked = false) {
            return {
                postId,
                likeCount: initialLikeCount,
                commentCount: initialCommentCount,
                apiUrl,
                likeUrl,
                isLiked: isInitiallyLiked,
                isLikingLoading: false,

                init() {
                    window.addEventListener('post-comment-count-changed', (event) => {
                        if ((event?.detail?.postId ?? null) !== this.postId) return;
                        if (typeof event.detail.count === 'number') {
                            this.commentCount = event.detail.count;
                        }
                    });
                },

                openPostModal(event) {
                    // Both article and comment button call this; buttons use @click.stop so
                    // only non-button areas bubble up through the article click handler.
                    const commentsUrl = this.apiUrl.replace('/api', '/comments');
                    window.dispatchEvent(new CustomEvent('post-modal-opened', {
                        detail: { postId: this.postId, apiUrl: this.apiUrl, commentsUrl }
                    }));
                },

                toggleLike() {
                    if (this.isLikingLoading) return;
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
</x-app-layout>