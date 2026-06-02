<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PostTrashController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the authenticated user's post trash bin.
     */
    public function index(Request $request)
    {
        $posts = Post::with(['community', 'flairs', 'media', 'event'])
            ->where('user_id', $request->user()->id)
            ->trashed()
            ->orderByDesc('trashed_at')
            ->paginate(15);

        return view('profile.trash', compact('posts'));
    }

    /**
     * Move a post to the trash (soft delete).
     */
    public function destroy(Request $request, Post $post)
    {
        $this->authorize('trash', $post);

        $post->update(['trashed_at' => now()]);

        return back()->with('success', 'Post moved to trash.');
    }

    /**
     * Restore a trashed post back to the feed.
     */
    public function restore(Request $request, Post $post)
    {
        $this->authorize('restore', $post);

        $post->update(['trashed_at' => null]);

        return back()->with('success', 'Post restored.');
    }

    /**
     * Permanently delete a trashed post (hard delete).
     */
    public function forceDelete(Request $request, Post $post)
    {
        $this->authorize('forceDelete', $post);

        $post->delete();

        return back()->with('success', 'Post permanently deleted.');
    }
}
