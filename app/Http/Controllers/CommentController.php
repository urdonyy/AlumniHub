<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Notifications\CommentRepliedNotification;
use App\Notifications\PostCommentedNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    use AuthorizesRequests;

    private function syncPostCommentCount(Post $post): void
    {
        $post->comment_count = Comment::query()
            ->where('post_id', $post->id)
            ->count();
        $post->save();
    }

    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request, $community = null, $post = null)
    {
        // Fetch the post model
        $postModel = Post::findOrFail($post);

        // A removed/trashed post is unavailable to view or interact with.
        abort_if($postModel->trashed_at !== null, 404);

        // Verify user can view this post
        $this->authorize('view', $postModel);

        // Verify user can create comments
        $this->authorize('create', [Comment::class, $postModel]);

        $validated = $request->validate([
            'body' => 'required|string|min:1|max:5000',
            'parent_comment_id' => 'sometimes|integer|exists:comments,id',
        ]);

        // Resolve the reply target. Threads are capped at 3 visual levels: a reply
        // to a level-3 (depth-2) comment is re-parented to its level-2 ancestor so
        // it appears as another level-3 reply rather than nesting deeper. We still
        // notify the person actually replied to ($repliedToComment).
        $repliedToComment = null;
        $parentComment = null;
        if ($request->has('parent_comment_id')) {
            $repliedToComment = Comment::find($request->parent_comment_id);

            if (!$repliedToComment || $repliedToComment->post_id !== $postModel->id) {
                return response()->json(['error' => 'Invalid parent comment'], 422);
            }

            $parentComment = $repliedToComment;
            while ($parentComment->getDepth() >= 2 && $parentComment->parentComment) {
                $parentComment = $parentComment->parentComment;
            }
        }

        $comment = $postModel->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'parent_comment_id' => $parentComment?->id,
        ]);

        $comment->load('user');

        $this->syncPostCommentCount($postModel);

        // Notify the post author (skip notifying self)
        if ((int) $postModel->user_id !== (int) $request->user()->id) {
            $postModel->user->notify(new PostCommentedNotification(
                post: $postModel,
                comment: $comment,
                actor: $request->user(),
            ));
        }

        // Notify the person actually replied to (the target comment's author),
        // even when the reply was re-parented to flatten the thread.
        if ($repliedToComment && (int) $repliedToComment->user_id !== (int) $request->user()->id) {
            $repliedToComment->user->notify(new CommentRepliedNotification(
                post: $postModel,
                comment: $comment,
                actor: $request->user(),
            ));
        }

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                ],
                'body' => $comment->body,
                'created_at' => $comment->created_at->diffForHumans(),
                'parent_comment_id' => $comment->parent_comment_id,
                'can_delete' => true,
                'replies' => [],
            ],
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy($community = null, $post = null, $comment = null)
    {
        $postModel = Post::findOrFail($post);

        // A removed/trashed post is unavailable to view or interact with.
        abort_if($postModel->trashed_at !== null, 404);
        $commentModel = Comment::findOrFail($comment);

        // Verify comment belongs to this post
        if ($commentModel->post_id !== $postModel->id) {
            abort(404, 'Comment not found on this post');
        }

        $this->authorize('delete', $commentModel);

        $commentModel->delete();

        $this->syncPostCommentCount($postModel);

        return response()->json(['success' => true]);
    }

    /**
     * Get post details with comments (JSON response).
     */
    public function getPostDetails($community = null, $post = null)
    {
        $postModel = Post::findOrFail($post);

        // A removed/trashed post is unavailable to view or interact with.
        abort_if($postModel->trashed_at !== null, 404);
        $this->authorize('view', $postModel);

        $postModel->load(['user', 'community', 'flairs', 'media', 'event']);

        return response()->json([
            'success' => true,
            'post' => [
                'id' => $postModel->id,
                'title' => $postModel->title,
                'body_markdown' => $postModel->body_markdown,
                'body_html' => $postModel->body_html,
                'visibility' => $postModel->visibility,
                'post_type' => $postModel->post_type,
                'event' => $postModel->event ? [
                    'event_type' => $postModel->event->event_type,
                    'starts_at' => $postModel->event->starts_at?->toIso8601String(),
                    'starts_at_human' => $postModel->event->starts_at?->format('M j, Y · g:i A'),
                    'ends_at' => $postModel->event->ends_at?->toIso8601String(),
                    'ends_at_human' => $postModel->event->ends_at?->format('M j, Y · g:i A'),
                    'external_link' => $postModel->event->external_link,
                    'address' => $postModel->event->address,
                    'venue' => $postModel->event->venue,
                ] : null,
                'like_count' => $postModel->like_count,
                'is_liked' => $postModel->isLikedByAuthUser(),
                'comment_count' => $postModel->comment_count,
                'created_at' => $postModel->created_at->diffForHumans(),
                'user' => [
                    'id' => $postModel->user->id,
                    'name' => $postModel->user->name,
                    'batch_year' => $postModel->user->batch_year,
                    'program_course' => $postModel->user->program_course,
                    'avatar_url' => $postModel->user->profileAvatarUrl(),
                ],
                'community' => $postModel->community ? [
                    'id' => $postModel->community->id,
                    'name' => $postModel->community->name,
                    'is_system' => (bool) $postModel->community->is_system,
                ] : null,
                'flairs' => $postModel->flairs->map(function ($flair) {
                    return [
                        'id' => $flair->id,
                        'name' => $flair->name,
                        'color' => $flair->color,
                        'icon' => $flair->icon,
                    ];
                })->toArray(),
                'media' => $postModel->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'url' => $media->url,
                        'alt_text' => $media->alt_text,
                    ];
                })->toArray(),
            ],
        ]);
    }

    /**
     * Personal (community-less) post variants.
     *
     * Connections-only posts have no community segment in their URL, so route
     * params bind positionally to a single {post}. These thin wrappers forward
     * to the shared handlers with a null community.
     */
    public function getPostDetailsForPost($post)
    {
        return $this->getPostDetails(null, $post);
    }

    public function indexForPost($post)
    {
        return $this->index(null, $post);
    }

    public function storeForPost(Request $request, $post)
    {
        return $this->store($request, null, $post);
    }

    public function destroyForPost($post, $comment)
    {
        return $this->destroy(null, $post, $comment);
    }

    /**
     * Get comments for a post (JSON response).
     */
    public function index($community = null, $post = null)
    {
        $postModel = Post::findOrFail($post);

        // A removed/trashed post is unavailable to view or interact with.
        abort_if($postModel->trashed_at !== null, 404);
        $this->authorize('view', $postModel);

        $comments = $postModel->comments()
            ->topLevel()
            ->with(['user', 'replies.user', 'replies.replies.user'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'comments' => $comments->map(function ($comment) {
                return $this->formatComment($comment);
            }),
        ]);
    }
    private function formatComment(Comment $comment): array
    {
        return [
            'id' => $comment->id,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
            ],
            'body' => $comment->body,
            'created_at' => $comment->created_at->diffForHumans(),
            'parent_comment_id' => $comment->parent_comment_id,
            'can_delete' => Gate::allows('delete', $comment),
            'replies' => $comment->replies->map(function ($reply) {
                return $this->formatComment($reply);
            })->toArray(),
        ];
    }
}
