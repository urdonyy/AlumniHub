<div x-data="postDetailModal()"
    @post-modal-opened.window="openModal($event.detail.postId, $event.detail.apiUrl, $event.detail.commentsUrl)"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" x-show="isOpen" x-transition.opacity
    @keydown.escape.window="closeModal()" style="display: none;">

    <!-- Modal content -->
    <div @click.away="closeModal()"
        class="w-full max-w-4xl max-h-screen overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-2xl">

        <!-- Modal header -->
        <div class="sticky top-0 flex items-center justify-between border-b border-gray-100 bg-white px-6 py-4 z-40">
            <h2 class="text-lg font-semibold text-gray-900">Post Details</h2>
            <button type="button" @click="closeModal()"
                class="h-8 w-8 rounded-full bg-gray-100 text-lg leading-none hover:bg-gray-200">
                ×
            </button>
        </div>

        <!-- Modal body -->
        <div class="p-6 space-y-6">
            <!-- Loading state -->
            <div x-show="isLoading" class="flex justify-center py-12">
                <div class="text-gray-500">Loading post details...</div>
            </div>

            <!-- Post content -->
            <div x-show="!isLoading && post" class="space-y-6">
                <!-- Post header -->
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">
                                <span x-text="post.title"></span>
                            </h3>
                            <p class="mt-2 text-sm text-gray-600">
                                By <span x-text="post.user?.name"></span>
                                in <span x-text="post.community?.name"></span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <span x-text="post.created_at"></span>
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
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

                    <!-- Reactions summary -->
                    <div class="flex items-center gap-4 text-sm text-gray-600">
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span x-text="post.like_count ?? 0"></span>
                        </span>
                    </div>
                </div>

                <!-- Post body -->
                <div class="prose prose-sm max-w-none text-gray-700">
                    <div x-html="post.body_html"></div>
                </div>

                <!-- Post media -->
                <template x-if="post.media && post.media.length > 0">
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                        <template x-for="image in post.media" :key="image.id">
                            <img :src="`/storage/${image.path}`" :alt="image.alt_text || 'Post image'"
                                class="rounded-lg border border-gray-200">
                        </template>
                    </div>
                </template>

                <!-- Comments section -->
                <div class="border-t border-gray-100 pt-6">
                    <x-comments-section />
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

<script>
    function postDetailModal() {
        return {
            isOpen: false,
            isLoading: false,
            post: null,
            error: null,

            init() {
                window.addEventListener('post-like-count-changed', (event) => {
                    if (!this.isOpen || !this.post) return;
                    if ((event?.detail?.postId ?? null) !== this.post.id) return;
                    if (typeof event.detail.count === 'number') {
                        this.post.like_count = event.detail.count;
                    }
                });
            },

            openModal(postId, apiUrl, commentsUrl) {
                this.isOpen = true;
                this.isLoading = true;
                this.error = null;
                this.post = null;

                // Fetch post details via the API endpoint
                fetch(apiUrl)
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to load post');
                        return response.json();
                    })
                    .then(data => {
                        this.post = data.post;
                        this.isLoading = false;

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
            }
        };
    }
</script>