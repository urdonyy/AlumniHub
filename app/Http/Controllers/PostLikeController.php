<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Notifications\PostLikedNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    use AuthorizesRequests;
    /**
     * Toggle a like on a post.
     */
    public function toggle(Request $request, $community, $post)
    {
        $postModel = Post::findOrFail($post);

        // Verify user can view this post
        $this->authorize('view', $postModel);

        $user = $request->user();

        // Check if user already liked this post
        $like = Like::where('post_id', $postModel->id)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            // Unlike the post
            $like->delete();
            $postModel->decrement('like_count');
            $liked = false;
        } else {
            // Like the post
            Like::create([
                'post_id' => $postModel->id,
                'user_id' => $user->id,
            ]);
            $postModel->increment('like_count');
            $liked = true;

            // Notify the post author (skip notifying self)
            if ((int) $postModel->user_id !== (int) $user->id) {
                $postModel->loadMissing('user');
                $postModel->user->notify(new PostLikedNotification(
                    post: $postModel,
                    actor: $user,
                ));
            }
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'like_count' => $postModel->like_count,
        ]);
    }
}
