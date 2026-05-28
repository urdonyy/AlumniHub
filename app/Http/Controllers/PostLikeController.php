<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Notifications\PostLikedNotification;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    use AuthorizesRequests;

    private function syncPostLikeCount(Post $post): void
    {
        $post->like_count = Like::query()
            ->where('post_id', $post->id)
            ->count();
        $post->save();
        $post->refresh();
    }
    /**
     * Toggle a like on a post.
     */
    public function toggle(Request $request, $community, $post)
    {
        $postModel = Post::findOrFail($post);

        // Verify user can view this post
        $this->authorize('view', $postModel);

        $user = $request->user();

        // Only verified accounts may react to posts.
        if (! $user->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Verify your account to interact with posts.',
            ], 403);
        }

        // Check if user already liked this post
        $like = Like::where('post_id', $postModel->id)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            // Unlike the post
            $like->delete();
            $liked = false;
        } else {
            // Like the post
            $createdLike = false;
            try {
                Like::create([
                    'post_id' => $postModel->id,
                    'user_id' => $user->id,
                ]);
                $createdLike = true;
            } catch (QueryException $e) {
                // In rare race conditions, the unique constraint may be hit; treat as already-liked.
                if ((string) $e->getCode() !== '23000') {
                    throw $e;
                }
            }

            $liked = true;

            // Notify the post author (skip notifying self)
            if ($createdLike && (int) $postModel->user_id !== (int) $user->id) {
                $postModel->loadMissing('user');
                $postModel->user->notify(new PostLikedNotification(
                    post: $postModel,
                    actor: $user,
                ));
            }
        }

        $this->syncPostLikeCount($postModel);

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'like_count' => $postModel->like_count,
        ]);
    }
}
