<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Post;
use App\Services\EventInviteService;
use App\Services\PostService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CommunityPostController extends Controller
{
    use AuthorizesRequests;

    protected PostService $postService;

    protected EventInviteService $eventInviteService;

    public function __construct(PostService $postService, EventInviteService $eventInviteService)
    {
        $this->postService = $postService;
        $this->eventInviteService = $eventInviteService;
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
        $isOutsiderViewingProgramBatch = ! $isMember && ($community->isProgramBatch() || $community->is_system);

        $postsQuery = $community->posts()
            ->published()
            ->with(['user', 'flairs', 'media', 'event'])
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
        $post->load(['user', 'flairs', 'media', 'event']);

        return view('communities.posts.show', compact('community', 'post'));
    }

    public function create(Community $community)
    {
        $this->authorize('create', [Post::class, $community]);

        $flairs = $community->flairs()
            ->forCommunity($community->id)
            ->selectable()
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
        $postType = $request->input('post_type', 'text');
        $isEvent = $postType === 'event';
        $isOnline = $request->input('event_type') === 'online';
        $isConnectionsOnly = $request->input('visibility') === 'connections';
        $communityId = $request->input('community_id');

        $community = $communityId ? Community::findOrFail($communityId) : null;

        $rules = [
            'post_type' => 'required|in:text,media,event',
            'community_id' => $isConnectionsOnly
                ? 'nullable|integer|exists:communities,id'
                : 'required|integer|exists:communities,id',
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
        ];

        if ($isEvent) {
            // Events: audience limited to community or connections (no public);
            // event name maps to title, description to body (optional).
            $rules += [
                'visibility' => 'required|string|in:members,connections',
                'event_type' => 'required|in:online,in_person',
                'title' => 'required|string|max:255',
                'body_markdown' => 'nullable|string|max:5000',
                'starts_at' => 'required|date',
                'ends_at' => 'nullable|date|after:starts_at',
                'external_link' => ($isOnline ? 'required' : 'nullable') . '|url|max:1024',
                'address' => ($isOnline ? 'nullable' : 'required') . '|string|max:255',
                'venue' => 'nullable|string|max:255',
                'auto_invite' => 'sometimes|boolean',
            ];
        } else {
            $rules += [
                'visibility' => 'sometimes|string|in:members,public,connections',
                'title' => 'nullable|string|max:255',
                'body_markdown' => 'required|string|min:3',
            ];
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        if ($community) {
            $this->authorize('create', [Post::class, $community]);
        }

        $post = $this->postService->createPost(
            community: $community,
            user: $request->user(),
            data: $validated
        );

        // Fan out auto-invites (email + web notification) to the event audience.
        if ($isEvent && $request->boolean('auto_invite')) {
            $this->eventInviteService->dispatch($post, $request->user());
        }

        return redirect()->route('dashboard')->with('success', 'Post created successfully!');
    }
}
