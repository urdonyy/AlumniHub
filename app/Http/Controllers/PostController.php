<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of posts in a community.
     */
    public function index(Community $community)
    {
        $posts = $community->posts()
            ->published()
            ->with(['user', 'flairs', 'media'])
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('communities.posts.index', compact('community', 'posts'));
    }

    /**
     * Display a specific post.
     */
    public function show(Community $community, Post $post)
    {
        // Check if post belongs to community
        if ($post->community_id !== $community->id) {
            abort(404, 'Post not found in this community');
        }

        // Check authorization to view
        $this->authorize('view', $post);

        // Increment view count
        $post->increment('view_count');

        $post->load(['user', 'flairs', 'media']);

        return view('communities.posts.show', compact('community', 'post'));
    }
}
