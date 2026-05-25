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
                    <aside class="space-y-6 lg:col-span-3">
                        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Profile') }}</p>
                            <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</h3>
                            <p class="text-sm text-gray-600">{{ auth()->user()->program_course ?? __('Program pending') }}</p>
                            <div
                                class="mt-3 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ auth()->user()->accountStatusBadgeClass() }}">
                                {{ auth()->user()->accountStatusLabel() }}
                            </div>
                            <a href="{{ route('profiles.show', auth()->id()) }}"
                                class="mt-4 inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
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
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm space-y-3" x-data="{ openComposer: false }">
    <button type="button"
        @click="openComposer = true"
        class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-left text-gray-500 transition hover:bg-gray-100">
        What's on your mind, {{ auth()->user()->name }}?
    </button>

    <div x-show="openComposer"
        x-transition.opacity
        @keydown.escape.window="openComposer = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
        style="display: none;">
        <div @click.away="openComposer = false"
            class="w-full max-w-2xl rounded-2xl border border-gray-700 bg-gray-900 text-gray-100 shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-700 px-5 py-4">
                <h3 class="text-xl font-semibold">Create post</h3>
                <button type="button" @click="openComposer = false"
                    class="h-9 w-9 rounded-full bg-gray-700 text-lg leading-none hover:bg-gray-600">
                    ×
                </button>
            </div>

        {{-- post creation modal --}}
            <form method="post" action="{{ route('posts.quick-store') }}"
                enctype="multipart/form-data" class="space-y-4 px-5 py-5">
                @csrf

                <div>
                    <label class="mb-1 block text-sm text-gray-300">Choose community</label>
                    <select name="community_id" required
                        class="w-full rounded-lg border border-gray-700 bg-gray-800 text-gray-100 focus:border-blue-500 focus:ring-blue-500">
                        @php
                            /** @var \Illuminate\Support\Collection<int, \App\Models\Community> $joinedCommunitiesCollection */
                            $joinedCommunitiesCollection = collect($joinedCommunities ?? []);
                            $defaultCommunityId = old('community_id')
                                ?? ($joinedCommunitiesCollection->firstWhere('system_key', 'general-alumni-hub')->id ?? null);
                        @endphp
                        <option value="">Select one</option>
                        @foreach ($joinedCommunitiesCollection as $joinedCommunity)
                            <option value="{{ $joinedCommunity->id }}"
                                @selected($defaultCommunityId == $joinedCommunity->id)>
                                {{ $joinedCommunity->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <input type="text" name="title" required
                        class="w-full rounded-lg border border-gray-700 bg-gray-800 text-gray-100 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Title">
                </div>

                <div>
                    <label class="mb-1 block text-sm text-gray-300">Audience</label>
                    <select name="visibility" required
                        class="w-full rounded-lg border border-gray-700 bg-gray-800 text-gray-100 focus:border-blue-500 focus:ring-blue-500">
                        <option value="members" @selected(old('visibility', 'members') === 'members')>Communities only</option>
                        <option value="connections" @selected(old('visibility') === 'connections')>Connections only</option>
                        <option value="public" @selected(old('visibility') === 'public')>Public</option>
                    </select>
                </div>

                @if(!empty($availableFlairs))
                    <div>
                        <label class="mb-1 block text-sm text-gray-300">Flair</label>
                        <x-flair-selector :flairs="$availableFlairs" :selected="old('flairs', [])" />
                    </div>
                @endif

                <div>
                    <textarea name="body_markdown" rows="7" required
                        class="w-full rounded-lg border border-gray-700 bg-gray-800 text-gray-100 placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="What's on your mind, {{ auth()->user()->name }}?"></textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm text-gray-300">Add images</label>
                    <input type="file" name="attachments[]" multiple accept="image/*"
                        class="block w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-200 file:mr-4 file:rounded-md file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-white hover:file:bg-blue-500">
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white hover:bg-blue-500">
                    Post
                </button>
            </form>
        </div>
    </div>

                        @if(isset($posts))
                            @foreach($posts as $post)
                                <article x-data="postCard({{ $post->id }}, {{ $post->like_count }}, {{ $post->comments_count ?? 0 }}, '{{ route('communities.posts.api', ['community' => $post->community, 'post' => $post]) }}', '{{ route('communities.posts.like', ['community' => $post->community, 'post' => $post]) }}', {{ $post->isLikedByAuthUser() ? 'true' : 'false' }})"
                                    @click="openPostModal($event)"
                                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm cursor-pointer transition hover:shadow-md hover:border-gray-300">
                                    
                                    <!-- Post Header -->
                                    <div class="p-5 pb-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h3 class="text-base font-semibold text-gray-900">{{ $post->user->name }}</h3>
                                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ $post->community?->name ?? __('Post') }}</p>
                                            </div>

                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                                {{ ucfirst($post->visibility) }}
                                            </span>
                                        </div>

                                        <h4 class="mt-3 text-lg font-semibold text-gray-900">{{ $post->title }}</h4>
                                        <p class="mt-2 text-sm leading-6 text-gray-700">{{ \Illuminate\Support\Str::limit(strip_tags($post->body_markdown), 200) }}</p>
                                        @if($post->flairs->count() > 0)
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach($post->flairs as $flair)
                                                    <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                                        <span class="inline-block h-1.5 w-1.5 rounded-full" style="background-color: {{ $flair->color ?? '#9ca3af' }}"></span>
                                                        {{ $flair->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Post Media -->
                                    @if($post->media->count() > 0)
                                        <div class="relative h-40 overflow-hidden bg-gray-100">
                                            <img src="/storage/{{ $post->media->first()->file_path }}" 
                                                alt="{{ $post->media->first()->alt_text ?? 'Post image' }}"
                                                class="h-full w-full object-cover">
                                            @if($post->media->count() > 1)
                                                <div class="absolute bottom-2 right-2 rounded bg-black/60 px-2 py-1 text-xs font-medium text-white">
                                                    +{{ $post->media->count() - 1 }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Post Footer -->
                                    <div class="flex items-center justify-between gap-2 border-t border-gray-100 px-5 py-3 text-xs text-gray-600">
                                        <div class="flex gap-4">
                                            <button type="button" @click.stop="toggleLike()" :class="isLiked ? 'text-red-600' : 'text-gray-600'"
                                                class="flex items-center gap-1 font-medium hover:text-red-600 transition">
                                                <svg class="h-4 w-4" :fill="isLiked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                <span x-text="likeCount"></span>
                                            </button>
                                            <span class="flex items-center gap-1">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2l-4 4z" />
                                                </svg>
                                                <span x-text="commentCount"></span>
                                            </span>
                                        </div>
                                        <span class="text-gray-400">Click to view</span>
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

                    <aside class="space-y-6 lg:col-span-3">
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
        function feedManager() {
            return {
                showModal: false,
                selectedPostId: null,
                apiUrl: null,
                commentsUrl: null,

                openPostModal(event, postId, apiUrl, commentsUrl) {
                    // Prevent default link behavior if any
                    event?.preventDefault();
                    
                    this.selectedPostId = postId;
                    this.apiUrl = apiUrl;
                    this.commentsUrl = commentsUrl;
                    this.showModal = true;
                    
                    // Dispatch event to load post and comments
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
                    if (event.target.closest('button')) {
                        return; // Don't open modal when clicking the like button
                    }
                    
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