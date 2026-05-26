<div x-data="postDetailModal()"
    @post-modal-opened.window="openModal($event.detail.postId, $event.detail.apiUrl, $event.detail.commentsUrl)"
    class="fixed inset-0 z-50 overflow-hidden" x-show="isOpen" x-transition.opacity
    @keydown.escape.window="closeModal()" style="display: none;">

    <button type="button" class="fixed inset-0 bg-black/60" @click="closeModal()"
        aria-label="Close modal backdrop"></button>

    <div class="relative z-10 flex h-full w-full items-start justify-center p-0 sm:p-4 sm:items-center">
        <!-- Modal content -->
        <div @click.away="closeModal()"
            class="relative w-full max-w-4xl sm:max-w-5xl h-[100dvh] sm:h-[90vh] overflow-hidden bg-white shadow-2xl sm:rounded-2xl sm:border sm:border-gray-200">

            <!-- Close button (keep icon, remove heading) -->
            <button type="button" @click="closeModal()"
                class="absolute left-3 top-3 z-50 flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-xl leading-none hover:bg-gray-200 sm:left-auto sm:right-3"
                aria-label="Close">
                ×
            </button>

            <!-- Modal body -->
            <div class="flex h-full min-h-0 flex-col">
            <!-- Loading state -->
                <div x-show="isLoading" class="flex justify-center py-12">
                    <div class="text-gray-500">Loading post details...</div>
                </div>

            <!-- Post content -->
                <div x-show="!isLoading && post" class="h-full min-h-0">
                    <div class="grid h-full min-h-0 grid-cols-1 grid-rows-1"
                        :class="(post?.media && post.media.length > 0) ? 'grid-rows-[auto_minmax(0,1fr)] lg:grid-rows-1 lg:grid-cols-2' : 'lg:grid-cols-1'">

                        <!-- Media carousel (left on large screens, top on mobile) -->
                        <template x-if="post.media && post.media.length > 0">
                            <div class="relative bg-black/95 p-3 sm:p-4 lg:p-5 min-h-0">
                                <div class="relative h-[55vh] sm:h-[70vh] lg:h-full overflow-hidden rounded-xl bg-black/40">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-full">
                                            <div class="flex transition-transform duration-300 ease-out"
                                                :style="`transform: translateX(-${activeMediaIndex * 100}%);`">
                                                <template x-for="image in post.media" :key="image.id">
                                                    <div class="w-full shrink-0 px-3 sm:px-6">
                                                        <div class="flex h-[55vh] sm:h-[70vh] lg:h-full items-center justify-center">
                                                            <div class="w-full rounded-xl bg-black/30 p-3 sm:p-4">
                                                                <img :src="`/storage/${image.path}`"
                                                                    :alt="image.alt_text || 'Post image'"
                                                                    class="mx-auto max-h-[45vh] sm:max-h-[60vh] lg:max-h-[75vh] max-w-full rounded-lg object-contain" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Carousel controls -->
                                    <template x-if="post.media.length > 1">
                                        <div>
                                            <button type="button" @click="prevMedia()"
                                                class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/10 px-3 py-2 text-white hover:bg-white/20"
                                                aria-label="Previous image">
                                                ‹
                                            </button>
                                            <button type="button" @click="nextMedia()"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/10 px-3 py-2 text-white hover:bg-white/20"
                                                aria-label="Next image">
                                                ›
                                            </button>
                                            <div
                                                class="absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-3 py-1 text-xs text-white">
                                                <span x-text="activeMediaIndex + 1"></span>/<span
                                                    x-text="post.media.length"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Details (right on large screens, bottom on mobile) -->
                        <div class="flex h-full min-h-0 flex-col">
                            <div class="z-10 border-b border-gray-100 bg-white p-4 sm:p-6 lg:p-7">
                                <div class="space-y-3">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <h3 class="text-xl font-bold text-gray-900 sm:text-2xl">
                                                <span x-text="post.title"></span>
                                            </h3>
                                            <p class="mt-2 text-sm text-gray-600">
                                                By <span x-text="post.user?.name"></span>
                                                in <span x-text="post.community?.name"></span>
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500">
                                                <span x-text="post.created_at"></span>
                                            </p>
                                        </div>
                                        <span
                                            class="shrink-0 inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                            <span x-text="post.visibility"></span>
                                        </span>
                                    </div>

                                    <!-- Flairs -->
                                    <template x-if="post.flairs && post.flairs.length > 0">
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="flair in post.flairs" :key="flair.id">
                                                <span
                                                    class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                    <span class="inline-block h-2 w-2 rounded-full"
                                                        :style="`background-color: ${flair.color || '#9ca3af'}`"></span>
                                                    <span x-text="flair.name"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-6 lg:p-7">
                                <div class="space-y-4">
                                    <!-- Title + description + badges (scrolls with comments) -->
                                    <div class="space-y-3">
                                        <!-- Post description (2-line ellipsis + See more) -->
                                        <div class="space-y-1">
                                            <div x-ref="postBody"
                                                x-html="post.body_html"
                                                class="text-sm text-gray-700"
                                                :class="isBodyExpanded ? '' : '[display:-webkit-box] [-webkit-line-clamp:2] [-webkit-box-orient:vertical] overflow-hidden'">
                                            </div>

                                            <button type="button" x-show="isBodyOverflowing"
                                                @click="isBodyExpanded = !isBodyExpanded; $nextTick(() => { if (!isBodyExpanded) refreshBodyOverflow(); })"
                                                class="text-sm font-medium text-gray-700 hover:text-gray-900">
                                                <span x-text="isBodyExpanded ? 'See less' : 'See more'"></span>
                                            </button>
                                        </div>

                                        <!-- Reactions summary (keep existing like + comment badges) -->
                                        <div class="flex items-center gap-4 text-sm text-gray-600">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                <span x-text="post.like_count ?? 0"></span>
                                            </span>

                                            <span class="inline-flex items-center gap-1">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 8h10M7 12h6m-5 8l-4-4H4a2 2 0 01-2-2V6a2 2 0 012-2h16a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                </svg>
                                                <span x-text="post.comment_count ?? post.comments_count ?? 0"></span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-100 pt-4">
                                        <x-comments-section />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            <!-- Error state -->
                <div x-show="!isLoading && error"
                    class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 text-sm">
                    <span x-text="error"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function postDetailModal() {
        return {
            isOpen: false,
            isLoading: false,
            post: null,
            error: null,
            activeMediaIndex: 0,
            isBodyExpanded: false,
            isBodyOverflowing: false,
            scrollLockPaddingRight: '',
            scrollLockScrollY: 0,
            scrollLockPosition: '',
            scrollLockTop: '',
            scrollLockLeft: '',
            scrollLockRight: '',
            scrollLockWidth: '',

            init() {
                window.addEventListener('post-like-count-changed', (event) => {
                    if (!this.isOpen || !this.post) return;
                    if ((event?.detail?.postId ?? null) !== this.post.id) return;
                    if (typeof event.detail.count === 'number') {
                        this.post.like_count = event.detail.count;
                    }
                });

                window.addEventListener('post-comment-count-changed', (event) => {
                    if (!this.isOpen || !this.post) return;
                    if ((event?.detail?.postId ?? null) !== this.post.id) return;
                    if (typeof event.detail.count === 'number') {
                        this.post.comment_count = event.detail.count;
                        this.post.comments_count = event.detail.count;
                    }
                });
            },

            refreshBodyOverflow() {
                this.isBodyOverflowing = false;
                if (this.isBodyExpanded) return;

                this.$nextTick(() => {
                    const el = this.$refs?.postBody;
                    if (!el) return;
                    this.isBodyOverflowing = (el.scrollHeight - el.clientHeight) > 2;
                });
            },

            lockScroll() {
                const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
                this.scrollLockScrollY = window.scrollY || window.pageYOffset || 0;
                this.scrollLockPaddingRight = document.body.style.paddingRight || '';
                this.scrollLockPosition = document.body.style.position || '';
                this.scrollLockTop = document.body.style.top || '';
                this.scrollLockLeft = document.body.style.left || '';
                this.scrollLockRight = document.body.style.right || '';
                this.scrollLockWidth = document.body.style.width || '';
                document.documentElement.classList.add('overflow-hidden');
                document.body.classList.add('overflow-hidden');
                document.body.style.position = 'fixed';
                document.body.style.top = `-${this.scrollLockScrollY}px`;
                document.body.style.left = '0';
                document.body.style.right = '0';
                document.body.style.width = '100%';
                if (scrollbarWidth > 0) {
                    document.body.style.paddingRight = `${scrollbarWidth}px`;
                }
            },

            unlockScroll() {
                document.documentElement.classList.remove('overflow-hidden');
                document.body.classList.remove('overflow-hidden');
                document.body.style.position = this.scrollLockPosition;
                document.body.style.top = this.scrollLockTop;
                document.body.style.left = this.scrollLockLeft;
                document.body.style.right = this.scrollLockRight;
                document.body.style.width = this.scrollLockWidth;
                document.body.style.paddingRight = this.scrollLockPaddingRight;
                this.scrollLockPaddingRight = '';
                const scrollY = this.scrollLockScrollY;
                this.scrollLockScrollY = 0;
                this.scrollLockPosition = '';
                this.scrollLockTop = '';
                this.scrollLockLeft = '';
                this.scrollLockRight = '';
                this.scrollLockWidth = '';
                window.scrollTo(0, scrollY);
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
                this.isBodyExpanded = false;
                this.isBodyOverflowing = false;
                this.lockScroll();

                // Fetch post details via the API endpoint
                fetch(apiUrl)
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to load post');
                        return response.json();
                    })
                    .then(data => {
                        this.post = data.post;
                        this.isLoading = false;
                        this.activeMediaIndex = 0;
                        this.isBodyExpanded = false;
                        this.refreshBodyOverflow();

                        // Dispatch event to load comments
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
                this.activeMediaIndex = 0;
                this.isBodyExpanded = false;
                this.isBodyOverflowing = false;
                this.unlockScroll();
            }
        };
    }
</script>