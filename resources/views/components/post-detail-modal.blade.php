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
    class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center"
    style="display: none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" @click="closeModal()"></div>

    {{-- Modal panel --}}
    <div class="relative z-10 flex w-full max-w-xl flex-col bg-white shadow-2xl
                h-[92dvh] sm:h-[88vh] sm:rounded-2xl sm:border sm:border-gray-200
                rounded-t-2xl">

        {{-- ── Header ── --}}
        <div class="shrink-0 border-b border-gray-100 px-4 py-3">
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
                    <img :src="post.user?.avatar_path ? `/storage/${post.user.avatar_path}` : '{{ asset('images/default-avatar.svg') }}'"
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
        <div class="flex-1 min-h-0 overflow-y-auto" x-ref="scrollBody">

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
                <div>
                    {{-- Community badge --}}
                    <div class="px-4 pt-4 pb-1">
                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-900 ring-1 ring-inset ring-red-200">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-text="post.community?.name"></span>
                        </span>
                    </div>

                    {{-- Title --}}
                    <template x-if="post.title">
                        <h2 class="px-4 pt-2 text-lg font-bold text-gray-900 leading-snug" x-text="post.title"></h2>
                    </template>

                    {{-- Flair tags --}}
                    <template x-if="post.flairs && post.flairs.length > 0">
                        <div class="flex flex-wrap gap-1.5 px-4 pt-2">
                            <template x-for="flair in post.flairs" :key="flair.id">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :style="`background-color:${flair.color ? flair.color + '20' : '#f3f4f6'};color:${flair.color || '#374151'};border:1px solid ${flair.color || '#e5e7eb'}`">
                                    <span x-text="flair.name"></span>
                                </span>
                            </template>
                        </div>
                    </template>

                    {{-- Body --}}
                    <div class="px-4 pt-3 pb-2">
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

                    {{-- Media grid --}}
                    <template x-if="post.media && post.media.length > 0">
                        <div class="mt-2 px-4">
                            <div class="overflow-hidden rounded-xl"
                                :class="{
                                    'grid grid-cols-1': post.media.length === 1,
                                    'grid grid-cols-2 gap-1': post.media.length === 2,
                                    'grid grid-cols-2 gap-1': post.media.length >= 3
                                }">

                                {{-- Single image --}}
                                <template x-if="post.media.length === 1">
                                    <img :src="`/storage/${post.media[0].path}`"
                                        :alt="post.media[0].alt_text || 'Post image'"
                                        class="w-full max-h-80 object-cover rounded-xl cursor-zoom-in"
                                        @click="activeMediaIndex = 0; lightboxOpen = true" />
                                </template>

                                {{-- Multiple images --}}
                                <template x-if="post.media.length > 1">
                                    <div :class="`grid gap-1 ${post.media.length === 2 ? 'grid-cols-2' : 'grid-cols-2'}`">
                                        <template x-for="(image, index) in post.media.slice(0, 4)" :key="image.id">
                                            <div class="relative cursor-zoom-in"
                                                @click="activeMediaIndex = index; lightboxOpen = true">
                                                <img :src="`/storage/${image.path}`"
                                                    :alt="image.alt_text || 'Post image'"
                                                    class="w-full object-cover rounded-lg"
                                                    :class="post.media.length === 2 ? 'h-52' : 'h-36'" />
                                                <template x-if="index === 3 && post.media.length > 4">
                                                    <div class="absolute inset-0 flex items-center justify-center rounded-lg bg-black/50">
                                                        <span class="text-xl font-bold text-white" x-text="`+${post.media.length - 4}`"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Reaction counts --}}
                    <div class="mx-4 mt-3 flex items-center justify-between border-y border-gray-100 py-2 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <span class="flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-white text-[10px]">♥</span>
                            <span x-text="(post.like_count ?? 0) + ((post.like_count ?? 0) === 1 ? ' like' : ' likes')"></span>
                        </span>
                        <span x-text="(post.comment_count ?? post.comments_count ?? 0) + ((post.comment_count ?? 0) === 1 ? ' comment' : ' comments')"></span>
                    </div>

                    {{-- Action buttons (Like / Comment) --}}
                    <div class="mx-4 flex border-b border-gray-100 pb-1">
                        <button type="button"
                            @click="$dispatch('post-like-toggle', { postId: post.id })"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            Like
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

            <img :src="`/storage/${post.media[activeMediaIndex].path}`"
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
                    if (!this.isOpen || !this.post) return;
                    if ((e?.detail?.postId ?? null) !== this.post.id) return;
                    if (typeof e.detail.count === 'number') {
                        this.post.comment_count = e.detail.count;
                        this.post.comments_count = e.detail.count;
                    }
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
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.top = `-${this.scrollLockScrollY}px`;
                document.body.style.left = '0';
                document.body.style.right = '0';
            },

            unlockScroll() {
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.left = '';
                document.body.style.right = '';
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
                this.lockScroll();

                fetch(apiUrl)
                    .then(r => {
                        if (!r.ok) throw new Error('Failed to load post');
                        return r.json();
                    })
                    .then(data => {
                        this.post = data.post;
                        this.isLoading = false;
                        this.$nextTick(() => {
                            this.refreshBodyOverflow();
                            if (this.$refs.scrollBody) this.$refs.scrollBody.scrollTop = 0;
                        });
                        window.dispatchEvent(new CustomEvent('post-comments-load', {
                            detail: { postId, commentsUrl }
                        }));
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
                this.unlockScroll();
            }
        };
    }
</script>
