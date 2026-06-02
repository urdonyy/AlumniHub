<div x-data="postDetailModal()"
    @post-modal-opened.window="openModal($event.detail.postId, $event.detail.apiUrl, $event.detail.commentsUrl)"
    @keydown.escape.window="closeModal()"
    x-show="isOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[500] flex items-end sm:items-center justify-center"
    style="display: none;">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-[2px]" @click="closeModal()"></div>

    {{-- Modal panel --}}
    <div class="relative z-10 flex w-full max-w-xl flex-col bg-white shadow-2xl overflow-hidden
                h-[92dvh] sm:h-[88vh] sm:rounded-2xl sm:border sm:border-gray-200
                rounded-t-2xl"
        :class="(post?.media?.length ?? 0) > 0 ? 'sm:max-w-5xl' : ''">

        {{-- ── Header ── --}}
        <div class="shrink-0 border-b border-gray-100 px-4 py-3"
            :class="(post?.media?.length ?? 0) > 0 ? 'sm:hidden' : ''">
            {{-- Loading skeleton --}}
            <template x-if="isLoading">
                <div class="flex items-center gap-3 animate-pulse">
                    <div class="h-11 w-11 rounded-full bg-gray-200"></div>
                    <div class="flex-1 space-y-1.5">
                        <div class="h-3.5 w-32 rounded bg-gray-200"></div>
                        <div class="h-3 w-48 rounded bg-gray-200"></div>
                    </div>
                </div>
            </template>

            {{-- Author row --}}
            <template x-if="!isLoading && post">
                <div class="flex items-start gap-3">
                    <img :src="post.user?.avatar_url ?? '{{ asset('images/default-avatar.svg') }}'"
                        :alt="post.user?.name"
                        class="h-11 w-11 shrink-0 rounded-full border border-gray-200 object-cover"
                        onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 leading-snug" x-text="post.user?.name"></p>
                        <p class="text-xs text-gray-500 leading-snug mt-0.5" x-text="authorMeta"></p>
                        <div class="mt-1 flex flex-wrap items-center gap-1.5">
                            <span class="text-xs text-gray-400" x-text="post.created_at"></span>
                            <span class="text-gray-300">·</span>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                :class="{
                                    'bg-green-50 text-green-700 ring-green-200': post.visibility === 'public',
                                    'bg-blue-50 text-blue-700 ring-blue-200': post.visibility === 'connections',
                                    'bg-gray-100 text-gray-600 ring-gray-200': post.visibility === 'members'
                                }"
                                x-text="post.visibility === 'public' ? 'Public' : post.visibility === 'connections' ? 'Connections' : 'Members'">
                            </span>
                        </div>
                    </div>
                    <button type="button" @click="closeModal()"
                        class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500 text-lg leading-none hover:bg-gray-200 transition"
                        aria-label="Close">
                        ×
                    </button>
                </div>
            </template>

            {{-- Close button during loading --}}
            <template x-if="isLoading">
                <button type="button" @click="closeModal()"
                    class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500 text-lg leading-none hover:bg-gray-200 transition"
                    aria-label="Close">×</button>
            </template>
        </div>

        {{-- ── Scrollable body ── --}}
        <div class="flex-1 min-h-0 overflow-y-auto" x-ref="scrollBody"
            :class="(post?.media?.length ?? 0) > 0 ? 'sm:overflow-hidden' : ''">

            {{-- Loading state --}}
            <template x-if="isLoading">
                <div class="flex flex-col items-center justify-center gap-3 py-16 text-gray-400">
                    <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <span class="text-sm">Loading post…</span>
                </div>
            </template>

            {{-- Error state --}}
            <template x-if="!isLoading && error">
                <div class="mx-4 mt-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                    <span x-text="error"></span>
                </div>
            </template>

            {{-- Post content --}}
            <template x-if="!isLoading && post">
                <div class="sm:min-h-0" :class="(post.media && post.media.length > 0) ? 'sm:flex sm:items-stretch sm:h-full sm:min-h-0' : ''">

                    {{-- Media carousel (mobile: above content) --}}
                    <template x-if="post.media && post.media.length > 0">
                        <div class="sm:hidden px-4 pt-4">
                            <div class="relative overflow-hidden rounded-xl bg-gray-100">
                                <img :src="post.media[activeMediaIndex].url"
                                    :alt="post.media[activeMediaIndex].alt_text || 'Post image'"
                                    class="w-full aspect-[4/5] object-cover cursor-zoom-in"
                                    @click="lightboxOpen = true" />

                                <template x-if="post.media.length > 1">
                                    <div>
                                        <button type="button" @click.stop="prevMedia()"
                                            class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-gray-900/60 px-3 py-2 text-white hover:bg-gray-900/20"
                                            aria-label="Previous image">
                                            ‹
                                        </button>
                                        <button type="button" @click.stop="nextMedia()"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-gray-900/60 px-3 py-2 text-white hover:bg-gray-900/20"
                                            aria-label="Next image">
                                            ›
                                        </button>
                                        <div
                                            class="absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full bg-gray-900/60 px-3 py-1 text-xs text-white">
                                            <span x-text="activeMediaIndex + 1"></span>/<span x-text="post.media.length"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Media carousel (desktop: fixed left column, centered) --}}
                    <template x-if="post.media && post.media.length > 0">
                        <div class="hidden sm:flex sm:w-1/2 sm:min-h-0 items-center justify-center bg-gray-900 px-4 py-4">
                            <div class="relative w-full flex items-center justify-center min-h-[60vh]">
                                <img :src="post.media[activeMediaIndex].url"
                                    :alt="post.media[activeMediaIndex].alt_text || 'Post image'"
                                    class="max-h-[60vh] max-w-full object-contain rounded-xl cursor-zoom-in"
                                    @click="lightboxOpen = true" />

                                <template x-if="post.media.length > 1">
                                    <div>
                                        <button type="button" @click.stop="prevMedia()"
                                            class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-black/50 px-3 py-2 text-white hover:bg-white/20"
                                            aria-label="Previous image">
                                            ‹
                                        </button>
                                        <button type="button" @click.stop="nextMedia()"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-black/50 px-3 py-2 text-white hover:bg-white/20"
                                            aria-label="Next image">
                                            ›
                                        </button>
                                        <div
                                            class="absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-3 py-1 text-xs text-white">
                                            <span x-text="activeMediaIndex + 1"></span>/<span x-text="post.media.length"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Right column: existing content (unchanged) --}}
                    <div :class="(post.media && post.media.length > 0) ? 'sm:w-1/2 sm:min-h-0 sm:flex sm:flex-col' : ''">

                        {{-- Right column sticky header (only for media posts on desktop) --}}
                        <template x-if="post.media && post.media.length > 0">
                            <div class="hidden sm:block shrink-0 sticky top-0 z-10 bg-white border-b border-gray-100 px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <img :src="post.user?.avatar_url ?? '{{ asset('images/default-avatar.svg') }}'"
                                        :alt="post.user?.name"
                                        class="h-11 w-11 shrink-0 rounded-full border border-gray-200 object-cover"
                                        onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-gray-900 leading-snug" x-text="post.user?.name"></p>
                                        <p class="text-xs text-gray-500 leading-snug mt-0.5" x-text="authorMeta"></p>
                                        <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                            <span class="text-xs text-gray-400" x-text="post.created_at"></span>
                                            <span class="text-gray-300">·</span>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                                :class="{
                                                    'bg-green-50 text-green-700 ring-green-200': post.visibility === 'public',
                                                    'bg-blue-50 text-blue-700 ring-blue-200': post.visibility === 'connections',
                                                    'bg-gray-100 text-gray-600 ring-gray-200': post.visibility === 'members'
                                                }"
                                                x-text="post.visibility === 'public' ? 'Public' : post.visibility === 'connections' ? 'Connections' : 'Members'">
                                            </span>
                                        </div>
                                    </div>
                                    <button type="button" @click="closeModal()"
                                        class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500 text-lg leading-none hover:bg-gray-200 transition"
                                        aria-label="Close">
                                        ×
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div x-ref="rightScrollBody"
                            :class="(post.media && post.media.length > 0) ? 'sm:flex-1 sm:min-h-0 sm:overflow-y-auto' : ''">
                            {{-- Community + Flair badges together --}}
                            <div class="flex flex-wrap items-center gap-1.5 px-4 pt-4 pb-1">
                        <template x-if="post.community">
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-900 ring-1 ring-inset ring-red-200">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span x-text="post.community?.name"></span>
                            </span>
                        </template>
                        <template x-if="post.flairs && post.flairs.length > 0">
                            <template x-for="flair in post.flairs" :key="flair.id">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :style="`background-color:${flair.color ? flair.color + '20' : '#f3f4f6'};color:${flair.color || '#374151'};border:1px solid ${flair.color || '#e5e7eb'}`">
                                    <span x-show="flair.icon" x-text="flair.icon"></span>
                                    <span x-text="flair.name"></span>
                                </span>
                            </template>
                        </template>
                    </div>

                    {{-- Title --}}
                    <template x-if="post.title">
                        <h2 class="px-4 pt-2 text-lg font-bold text-gray-900 leading-snug">
                            <span x-text="post.title"></span>
                        </h2>
                    </template>

                    {{-- Event details --}}
                    <template x-if="post.event">
                        <div class="mx-4 mt-3 rounded-xl border border-indigo-100 bg-indigo-50/60 px-3.5 py-3">
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-indigo-700 ring-1 ring-indigo-100">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 text-sm">
                                    <p class="font-semibold text-indigo-900" x-text="post.event.event_type === 'online' ? 'Online event' : 'In-person event'"></p>
                                    <p class="text-gray-700">
                                        <span x-text="post.event.starts_at_human"></span>
                                        <template x-if="post.event.ends_at_human">
                                            <span><span class="text-gray-400"> – </span><span x-text="post.event.ends_at_human"></span></span>
                                        </template>
                                    </p>
                                    <template x-if="post.event.event_type !== 'online' && post.event.address">
                                        <p class="mt-0.5 text-gray-700">📍 <span x-text="post.event.address"></span><template x-if="post.event.venue"><span> · <span x-text="post.event.venue"></span></span></template></p>
                                    </template>
                                    <template x-if="post.event.external_link">
                                        <a :href="post.event.external_link" target="_blank" rel="noopener"
                                            class="mt-0.5 inline-block break-all text-indigo-700 hover:underline" x-text="post.event.external_link"></a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Body --}}
                    <div class="px-4 pt-3 pb-2" x-show="post.body_html">
                        <div x-ref="postBody"
                            x-html="post.body_html"
                            class="prose prose-sm max-w-none text-gray-800 break-words"
                            :class="isBodyExpanded ? '' : 'line-clamp-5'">
                        </div>
                        <button x-show="isBodyOverflowing" type="button"
                            @click="isBodyExpanded = !isBodyExpanded; $nextTick(() => { if (!isBodyExpanded) refreshBodyOverflow(); })"
                            class="mt-1 text-sm font-semibold text-red-900 hover:underline">
                            <span x-text="isBodyExpanded ? 'See less' : 'See more'"></span>
                        </button>
                    </div>

                    {{-- Reaction counts --}}
                    <div class="mx-4 mt-3 flex items-center justify-between border-y border-gray-100 py-2 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <span class="flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-white text-[10px]">♥</span>
                            <span x-text="(post.like_count ?? 0) + ((post.like_count ?? 0) === 1 ? ' like' : ' likes')"></span>
                        </span>
                        <span x-text="(post.comment_count ?? post.comments_count ?? 0) + ((post.comment_count ?? 0) === 1 ? ' comment' : ' comments')"></span>
                    </div>

                    {{-- Unverified notice (read-only) --}}
                    <template x-if="!canInteract">
                        <div class="mx-4 my-2 flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                            <svg class="h-4 w-4 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span x-show="hasPendingDoc">Your verification is <strong>pending admin review</strong>. You'll gain full access once approved.</span>
                            <span x-show="!hasPendingDoc">
                                <a href="{{ route('profile.edit', ['section' => 'verification-document']) }}" class="font-semibold underline">Verify your account</a>
                                to like and comment on posts.
                            </span>
                        </div>
                    </template>

                    {{-- Action buttons (Like / Comment) --}}
                    <div x-show="canInteract" class="mx-4 flex border-b border-gray-100 pb-1">
                        <button type="button"
                            @click="toggleLike()"
                            :disabled="isLikingLoading"
                            :class="isLiked ? 'text-red-700' : 'text-gray-600'"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg py-2 text-sm font-semibold hover:bg-gray-50 transition disabled:opacity-60">
                            <svg class="h-4 w-4" :fill="isLiked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span x-text="isLiked ? 'Liked' : 'Like'"></span>
                        </button>
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('focus-comment-input'))"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2l-4 4z"/>
                            </svg>
                            Comment
                        </button>
                    </div>

                    {{-- Comments --}}
                    <div class="px-4 pb-4">
                        <x-comments-section />
                    </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ── Lightbox (full-screen image viewer) ── --}}
    <template x-if="lightboxOpen && post?.media?.length">
        <div class="fixed inset-0 z-[110] flex items-center justify-center bg-black/90"
            @click.self="lightboxOpen = false"
            @keydown.escape.window="lightboxOpen = false"
            @keydown.arrow-left.window="prevMedia()"
            @keydown.arrow-right.window="nextMedia()">

            <button type="button" @click="lightboxOpen = false"
                class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white text-xl hover:bg-white/20 transition">×</button>

            <template x-if="post.media.length > 1">
                <button type="button" @click="prevMedia()"
                    class="absolute left-4 top-1/2 -translate-y-1/2 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white text-2xl hover:bg-white/20 transition">‹</button>
            </template>

            <img :src="post.media[activeMediaIndex].url"
                :alt="post.media[activeMediaIndex].alt_text || 'Post image'"
                class="max-h-[90vh] max-w-[90vw] rounded-lg object-contain shadow-2xl">

            <template x-if="post.media.length > 1">
                <button type="button" @click="nextMedia()"
                    class="absolute right-4 top-1/2 -translate-y-1/2 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white text-2xl hover:bg-white/20 transition">›</button>
            </template>

            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-4 py-1 text-xs text-white">
                <span x-text="activeMediaIndex + 1"></span>/<span x-text="post.media.length"></span>
            </div>
        </div>
    </template>
</div>

<script>
    function postDetailModal() {
        return {
            isOpen: false,
            isLoading: false,
            post: null,
            error: null,
            activeMediaIndex: 0,
            lightboxOpen: false,
            isBodyExpanded: false,
            isBodyOverflowing: false,
            scrollLockScrollY: 0,
            isLiked: false,
            isLikingLoading: false,
            likeUrl: null,
            canInteract: document.querySelector('meta[name="user-verified"]')?.content === '1',
            hasPendingDoc: document.querySelector('meta[name="user-pending-doc"]')?.content === '1',

            toggleLike() {
                if (!this.canInteract) return;
                if (this.isLikingLoading || !this.likeUrl) return;
                this.isLikingLoading = true;
                fetch(this.likeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                })
                .then(r => { if (!r.ok) throw new Error('Failed'); return r.json(); })
                .then(data => {
                    this.isLiked = data.liked;
                    if (this.post) this.post.like_count = data.like_count;
                    this.isLikingLoading = false;
                    window.dispatchEvent(new CustomEvent('post-like-count-changed', {
                        detail: { postId: this.post?.id, count: data.like_count, liked: data.liked }
                    }));
                })
                .catch(() => { this.isLikingLoading = false; });
            },

            get authorMeta() {
                if (!this.post?.user) return '';
                const batch = this.post.user.batch_year ? `Batch ${this.post.user.batch_year}` : null;
                const program = this.post.user.program_course
                    ? (this.post.user.program_course.match(/\(([^)]+)\)$/) || [])[1] || null
                    : null;
                return [batch, program].filter(Boolean).join(' · ');
            },

            init() {
                window.addEventListener('post-like-count-changed', (e) => {
                    if (!this.isOpen || !this.post) return;
                    if ((e?.detail?.postId ?? null) !== this.post.id) return;
                    if (typeof e.detail.count === 'number') this.post.like_count = e.detail.count;
                });
                window.addEventListener('post-comment-count-changed', (e) => {
                    const postId = Number(e?.detail?.postId);
                    const count = Number(e?.detail?.count);
                    if (!Number.isFinite(postId) || !Number.isFinite(count)) return;
                    if (postId !== Number(this.post?.id)) return;
                    if (!this.post) this.post = { id: postId };
                    this.post.comment_count = count;
                    this.post.comments_count = count;
                });
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

            lockScroll() {
                this.scrollLockScrollY = window.scrollY;
                const scrollBarWidth = window.innerWidth - document.documentElement.clientWidth;
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.top = `-${this.scrollLockScrollY}px`;
                document.body.style.left = '0';
                document.body.style.right = '0';
                document.body.style.width = '100%';
                document.body.style.paddingRight = scrollBarWidth > 0 ? `${scrollBarWidth}px` : '';
            },

            unlockScroll() {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.left = '';
                document.body.style.right = '';
                document.body.style.width = '';
                document.body.style.paddingRight = '';
                window.scrollTo(0, this.scrollLockScrollY);
                this.scrollLockScrollY = 0;
            },

            nextMedia() {
                const count = this.post?.media?.length ?? 0;
                if (count <= 1) return;
                this.activeMediaIndex = (this.activeMediaIndex + 1) % count;
            },

            prevMedia() {
                const count = this.post?.media?.length ?? 0;
                if (count <= 1) return;
                this.activeMediaIndex = (this.activeMediaIndex - 1 + count) % count;
            },

            openModal(postId, apiUrl, commentsUrl) {
                this.isOpen = true;
                this.isLoading = true;
                this.error = null;
                this.post = null;
                this.activeMediaIndex = 0;
                this.lightboxOpen = false;
                this.isBodyExpanded = false;
                this.isBodyOverflowing = false;
                this.isLiked = false;
                this.likeUrl = apiUrl.replace('/api', '/like');
                this.lockScroll();

                fetch(apiUrl)
                    .then(r => {
                        if (!r.ok) throw new Error('Failed to load post');
                        return r.json();
                    })
                    .then(data => {
                        this.post = data.post;
                        this.isLiked = data.post.is_liked ?? false;
                        this.isLoading = false;
                        // Broadcast the freshly-loaded state so feed cards that were
                        // rendered before an edit update to the true values.
                        window.dispatchEvent(new CustomEvent('post-like-count-changed', {
                            detail: { postId: data.post.id, count: data.post.like_count ?? 0, liked: this.isLiked }
                        }));
                        window.dispatchEvent(new CustomEvent('post-updated', {
                            detail: {
                                postId: data.post.id,
                                visibility: data.post.visibility,
                                title: data.post.title ?? '',
                                body: (data.post.body_html ?? '').replace(/<[^>]*>/g, '').trim(),
                            }
                        }));
                        this.$nextTick(() => {
                            this.refreshBodyOverflow();
                            const scrollEl = this.$refs?.rightScrollBody || this.$refs?.scrollBody;
                            if (scrollEl) scrollEl.scrollTop = 0;
                            window.dispatchEvent(new CustomEvent('post-comments-load', {
                                detail: { postId, commentsUrl }
                            }));
                        });
                    })
                    .catch(err => {
                        this.error = err.message || 'Error loading post';
                        this.isLoading = false;
                    });
            },

            closeModal() {
                this.isOpen = false;
                this.post = null;
                this.error = null;
                this.isLoading = false;
                this.lightboxOpen = false;
                this.activeMediaIndex = 0;
                this.isBodyExpanded = false;
                this.isBodyOverflowing = false;
                this.isLiked = false;
                this.likeUrl = null;
                this.unlockScroll();
            }
        };
    }
</script>
