<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CommunityPostController extends Controller
{
    use AuthorizesRequests;

    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index(Community $community)
    {
        $posts = $community->posts()
            ->published()
            ->with(['user', 'flairs', 'media'])
            ->orderByDesc('pinned')
            ->orderByDesc('published_at')
            ->paginate(15);

        $flairs = $community->flairs()
            ->forCommunity($community->id)
            ->orderBy('name')
            ->get();

        return view('communities.posts.index', compact('community', 'posts', 'flairs'));
    }

    public function show(Community $community, Post $post)
    {
        if ($post->community_id !== $community->id) {
            abort(404, 'Post not found in this community');
        }

        $this->authorize('view', $post);

        $post->increment('view_count');
        $post->load(['user', 'flairs', 'media']);

        return view('communities.posts.show', compact('community', 'post'));
    }

    public function create(Community $community)
    {
        $this->authorize('create', [Post::class, $community]);

        $flairs = $community->flairs()
            ->forCommunity($community->id)
            ->orderBy('name')
            ->get();

        return view('communities.posts.create', compact('community', 'flairs'));
    }

    public function store(Request $request, Community $community)
    {
        $this->authorize('create', [Post::class, $community]);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'body_markdown' => 'required|string|min:3',
            'visibility' => 'sometimes|string|in:members,public,connections',
            'flairs' => 'sometimes|array',
            'flairs.*' => [
                'integer',
                Rule::exists('flairs', 'id')->where(function ($query) use ($community) {
                    $query->where('community_id', $community->id)
                        ->orWhereNull('community_id');
                }),
            ],
            'attachments' => 'sometimes|array',
            'attachments.*' => 'image|mimes:jpeg,png,gif,jpg|max:5120',
        ]);

        $this->postService->createPost(
            community: $community,
            user: $request->user(),
            data: $validated
        );

        return redirect()->route('communities.posts.index', $community)
            ->with('success', 'Post created successfully!');
    }

    public function edit(Community $community, Post $post)
    {
        if ($post->community_id !== $community->id) {
            abort(404, 'Post not found');
        }

        $this->authorize('update', $post);

        $flairs = $community->flairs()
            ->forCommunity($community->id)
            ->orderBy('name')
            ->get();

        $post->load('flairs', 'media');

        return view('communities.posts.edit', compact('community', 'post', 'flairs'));
    }

    public function update(Request $request, Community $community, Post $post)
    {
        if ($post->community_id !== $community->id) {
            abort(404, 'Post not found');
        }

        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'body_markdown' => 'required|string|min:3',
            'visibility' => 'sometimes|string|in:members,public,connections',
            'flairs' => 'sometimes|array',
            'flairs.*' => [
                'integer',
                Rule::exists('flairs', 'id')->where(function ($query) use ($community) {
                    $query->where('community_id', $community->id)
                        ->orWhereNull('community_id');
                }),
            ],
            'attachments' => 'sometimes|array',
            'attachments.*' => 'image|mimes:jpeg,png,gif,jpg|max:5120',
        ]);

        $this->postService->updatePost($post, $validated);

        return redirect()->route('communities.posts.index', $community)
            ->with('success', 'Post updated successfully!');
    }

    public function destroy(Community $community, Post $post)
    {
        if ($post->community_id !== $community->id) {
            abort(404, 'Post not found');
        }

        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('communities.posts.index', $community)
            ->with('success', 'Post deleted successfully!');
    }

    public function quickStore(Request $request)
    {
        $community = Community::findOrFail($request->input('community_id'));

        $validated = Validator::make($request->all(), [
            'community_id' => 'required|integer|exists:communities,id',
            'title' => 'nullable|string|max:255',
            'body_markdown' => 'required|string|min:3',
            'visibility' => 'sometimes|string|in:members,public,connections',
            'flairs' => 'sometimes|array',
            'flairs.*' => [
                'integer',
                Rule::exists('flairs', 'id')->where(function ($query) use ($community) {
                    $query->where('community_id', $community->id)
                        ->orWhereNull('community_id');
                }),
            ],
            'attachments' => 'sometimes|array',
            'attachments.*' => 'image|mimes:jpeg,png,gif,jpg|max:5120',
        ])->validate();

        $this->authorize('create', [Post::class, $community]);

        $this->postService->createPost(
            community: $community,
            user: $request->user(),
            data: $validated
        );

        return redirect()->route('communities.posts.index', $community)
            ->with('success', 'Post created successfully!');
    }
}
