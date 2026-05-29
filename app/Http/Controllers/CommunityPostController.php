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

    public function index(Request $request, Community $community)
    {
        // Unverified users may not browse community posts.
        if (! $request->user()->isVerified()) {
            return redirect()
                ->route('communities.show', $community)
                ->with('error', 'Verify your account to view posts in this community.');
        }

        $user = $request->user();
        $isMember = $community->members()->whereKey($user->id)->exists();
        $isOutsiderViewingProgramBatch = $community->isProgramBatch() && ! $isMember;

        $postsQuery = $community->posts()
            ->published()
            ->with(['user', 'flairs', 'media'])
            ->orderByDesc('pinned')
            ->orderByDesc('published_at');

        if ($isOutsiderViewingProgramBatch) {
            $postsQuery->where('visibility', 'public');
        }

        $posts = $postsQuery->paginate(15);

        $flairs = $community->flairs()
            ->forCommunity($community->id)
            ->orderBy('name')
            ->get();

        return view('communities.posts.index', compact(
            'community',
            'posts',
            'flairs',
            'isMember',
            'isOutsiderViewingProgramBatch',
        ));
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
        $isConnectionsOnly = $request->input('visibility') === 'connections';
        $communityId = $request->input('community_id');

        $community = $communityId ? Community::findOrFail($communityId) : null;

        $validated = Validator::make($request->all(), [
            'community_id' => $isConnectionsOnly
                ? 'nullable|integer|exists:communities,id'
                : 'required|integer|exists:communities,id',
            'title' => 'nullable|string|max:255',
            'body_markdown' => 'required|string|min:3',
            'visibility' => 'sometimes|string|in:members,public,connections',
            'flairs' => 'sometimes|array',
            'flairs.*' => [
                'integer',
                Rule::exists('flairs', 'id')->where(function ($query) use ($community) {
                    if ($community) {
                        $query->where('community_id', $community->id)
                            ->orWhereNull('community_id');
                    } else {
                        $query->whereNull('community_id');
                    }
                }),
            ],
            'attachments' => 'sometimes|array',
            'attachments.*' => 'image|mimes:jpeg,png,gif,jpg|max:5120',
        ])->validate();

        if ($community) {
            $this->authorize('create', [Post::class, $community]);
        }

        $this->postService->createPost(
            community: $community,
            user: $request->user(),
            data: $validated
        );

        return redirect()->route('dashboard')->with('success', 'Post created successfully!');
    }
}
