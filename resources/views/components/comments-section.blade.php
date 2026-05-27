<div x-data="commentsSection()"
    @post-comments-load.window="loadComments($event.detail.postId, $event.detail.commentsUrl)" class="space-y-4">

    <!-- Comments header -->
    <h3 class="font-semibold text-gray-900">
        Comments (<span x-text="totalComments"></span>)
    </h3>

    <!-- Error alert -->
    <template x-if="error">
        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            <span x-text="error"></span>
        </div>
    </template>

    <!-- Success alert -->
    <template x-if="successMessage">
        <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
            <span x-text="successMessage"></span>
        </div>
    </template>

    <!-- Comment form -->
    <textarea x-model="newComment.body" placeholder="Add a comment..." rows="3"
        x-ref="commentInput"
        @focus-comment-input.window="$el.focus(); $el.scrollIntoView({ behavior: 'smooth', block: 'center' })"
        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-900 focus:ring-1 focus:ring-red-900">
    </textarea>
    <div class="mt-3 flex justify-end gap-2">
        <button type="button" @click="newComment.body = ''"
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
            Cancel
        </button>
        <button type="button" @click="submitComment()" :disabled="!newComment.body.trim() || isSubmitting"
            class="rounded-lg bg-red-900 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:bg-gray-300">
            Post Comment
        </button>
    </div>

    <!-- Comments list -->
    <div class="space-y-4">
        <template x-for="comment in comments" :key="comment.id">
            <div class="rounded-lg border border-gray-100 p-4">
                <!-- Comment content -->
                <div class="flex gap-3">
                    <div class="flex-1">
                        <div class="flex items-baseline gap-2">
                            <p class="font-semibold text-gray-900">
                                <span x-text="comment.user?.name"></span>
                            </p>
                            <p class="text-xs text-gray-500">
                                <span x-text="comment.created_at"></span>
                            </p>
                        </div>
                        <p class="flex w-full text-left mt-2 text-sm text-gray-700 whitespace-pre-wrap">
                            <span x-text="comment.body"></span>
                        </p>

                        <!-- Action buttons -->
                        <div class="mt-3 flex gap-3 text-xs">
                            <button type="button" @click="toggleReplyForm(comment.id)"
                                class="text-blue-600 hover:text-blue-700">
                                Reply
                            </button>
                            <template x-if="comment.can_delete">
                                <button type="button" @click="deleteComment(comment.id)"
                                    class="text-red-600 hover:text-red-700">
                                    Delete
                                </button>
                            </template>
                        </div>

                        <!-- Reply form (if toggled) -->
                        <template x-if="replyingTo === comment.id">
                            <div class="mt-4 space-y-3 rounded-lg bg-gray-50 p-3">
                                <textarea x-model="replyComment.body" placeholder="Write a reply..." rows="2"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-900 focus:ring-1 focus:ring-red-900">
                                </textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="toggleReplyForm(null)"
                                        class="rounded-lg border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100">
                                        Cancel
                                    </button>
                                    <button type="button" @click="submitReply(comment.id)"
                                        :disabled="!replyComment.body.trim() || isSubmitting"
                                        class="rounded-lg bg-red-900 px-3 py-1 text-xs font-medium text-white hover:bg-red-700 disabled:bg-gray-300">
                                        Reply
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Nested replies -->
                        <template x-if="comment.replies && comment.replies.length > 0">
                            <div class="mt-4 space-y-3 border-l-2 border-gray-200 pl-4">
                                <template x-for="reply in comment.replies" :key="reply.id">
                                    <!-- Nested reply -->
                                    <div class="rounded-lg bg-gray-50 p-3">
                                        <div class="flex gap-3">
                                            <div class="flex-1">
                                                <div class="flex items-baseline gap-2">
                                                    <p class="font-semibold text-sm text-gray-900">
                                                        <span x-text="reply.user?.name"></span>
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        <span x-text="reply.created_at"></span>
                                                    </p>
                                                </div>
                                                <p class="flex w-full text-left mt-2 text-sm text-gray-700 whitespace-pre-wrap">
                                                    <span x-text="reply.body"></span>
                                                </p>
                                                <div class="mt-2 flex gap-3 text-xs">
                                                    <template x-if="reply.can_delete">
                                                        <button type="button" @click="deleteComment(reply.id)"
                                                            class="text-red-600 hover:text-red-700">
                                                            Delete
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- No comments -->
        <template x-if="!isLoading && comments.length === 0">
            <p class="text-center text-sm text-gray-500 py-8">
                No comments yet. Be the first to comment!
            </p>
        </template>

        <!-- Loading -->
        <template x-if="isLoading">
            <p class="text-center text-sm text-gray-500 py-8">
                Loading comments...
            </p>
        </template>
    </div>
</div>

<script>
    function commentsSection() {
        return {
            comments: [],
            newComment: { body: '' },
            replyComment: { body: '' },
            replyingTo: null,
            isLoading: false,
            isSubmitting: false,
            postId: null,
            commentsUrl: null,
            error: null,
            successMessage: null,

            get totalComments() {
                return this.countAll(this.comments);
            },

            countAll(comments) {
                return (comments || []).reduce((acc, comment) => {
                    const replies = comment?.replies || [];
                    return acc + 1 + this.countAll(replies);
                }, 0);
            },

            dispatchCountChanged() {
                if (!this.postId) return;
                const postId = Number(this.postId);
                const count = Number(this.totalComments ?? 0);
                if (!Number.isFinite(postId) || !Number.isFinite(count)) return;
                window.dispatchEvent(new CustomEvent('post-comment-count-changed', {
                    detail: { postId, count, source: 'comments' }
                }));
            },

            removeFromTree(items, targetId) {
                if (!Array.isArray(items)) return items;
                return items
                    .filter(item => item?.id !== targetId)
                    .map(item => ({
                        ...item,
                        replies: this.removeFromTree(item?.replies || [], targetId),
                    }));
            },

            loadComments(postId, commentsUrl) {
                this.isLoading = true;
                this.error = null;
                this.postId = postId;
                this.commentsUrl = commentsUrl;

                fetch(commentsUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to load comments');
                        return response.json();
                    })
                    .then(data => {
                        this.comments = data.comments || [];
                        this.isLoading = false;
                        this.dispatchCountChanged();
                    })
                    .catch(err => {
                        console.error('Error loading comments:', err);
                        this.error = 'Failed to load comments. Please refresh the page.';
                        this.isLoading = false;
                    });
            },

            submitComment() {
                if (!this.newComment.body.trim()) return;

                this.isSubmitting = true;
                this.error = null;
                this.successMessage = null;
                const url = this.commentsUrl;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: JSON.stringify({
                        body: this.newComment.body,
                    }),
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                try {
                                    const data = JSON.parse(text);
                                    throw new Error(data.error || data.message || 'Failed to post comment');
                                } catch (e) {
                                    throw new Error('Failed to post comment: ' + response.status);
                                }
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.error || 'Comment posted but response was invalid');
                        }
                        this.comments.unshift(data.comment);
                        this.newComment.body = '';
                        this.successMessage = 'Comment posted!';
                        this.isSubmitting = false;
                        this.dispatchCountChanged();
                        setTimeout(() => this.successMessage = null, 3000);
                    })
                    .catch(err => {
                        console.error('Error posting comment:', err);
                        this.error = err.message || 'Failed to post comment. Please ensure you are a verified member of this community.';
                        this.isSubmitting = false;
                    });
            },

            submitReply(parentCommentId) {
                if (!this.replyComment.body.trim()) return;

                this.isSubmitting = true;
                this.error = null;
                this.successMessage = null;
                const url = this.commentsUrl;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: JSON.stringify({
                        body: this.replyComment.body,
                        parent_comment_id: parentCommentId,
                    }),
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                try {
                                    const data = JSON.parse(text);
                                    throw new Error(data.error || data.message || 'Failed to post reply');
                                } catch (e) {
                                    throw new Error('Failed to post reply: ' + response.status);
                                }
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.error || 'Reply posted but response was invalid');
                        }
                        // Add reply to the parent comment
                        const parentComment = this.comments.find(c => c.id === parentCommentId);
                        if (parentComment) {
                            if (!parentComment.replies) parentComment.replies = [];
                            parentComment.replies.push(data.comment);
                        }
                        this.replyComment.body = '';
                        this.replyingTo = null;
                        this.successMessage = 'Reply posted!';
                        this.isSubmitting = false;
                        this.dispatchCountChanged();
                        setTimeout(() => this.successMessage = null, 3000);
                    })
                    .catch(err => {
                        console.error('Error posting reply:', err);
                        this.error = err.message || 'Failed to post reply. Please ensure you are a verified member of this community.';
                        this.isSubmitting = false;
                    });
            },

            toggleReplyForm(commentId) {
                this.replyingTo = this.replyingTo === commentId ? null : commentId;
                this.replyComment.body = '';
                this.error = null;
            },

            deleteComment(commentId) {
                if (!confirm('Are you sure you want to delete this comment?')) return;

                const url = `${this.commentsUrl}/${commentId}`;

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(() => {
                                throw new Error('Failed to delete comment: ' + response.status);
                            });
                        }
                        return response.json();
                    })
                    .then(() => {
                        // Remove comment from list
                        this.comments = this.removeFromTree(this.comments, commentId);
                        this.successMessage = 'Comment deleted.';
                        this.dispatchCountChanged();
                        setTimeout(() => this.successMessage = null, 2000);
                    })
                    .catch(err => {
                        console.error('Error deleting comment:', err);
                        this.error = err.message || 'Failed to delete comment';
                    });
            }
        };
    }
</script>