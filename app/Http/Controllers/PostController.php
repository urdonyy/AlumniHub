<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected PostService $postService) {}
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
     * Update an existing post (author edit via composer re-open).
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $postType = $post->post_type;
        $isOnline = $request->input('event_type') === 'online';
        $isConnectionsOnly = $request->input('visibility') === 'connections';

        $communityId = $request->input('community_id');
        $community   = $communityId ? Community::find($communityId) : null;

        $rules = [
            'community_id' => $isConnectionsOnly
                ? 'nullable|integer|exists:communities,id'
                : 'required|integer|exists:communities,id',
            'title'        => $postType === 'event' ? 'required|string|max:255' : 'nullable|string|max:255',
            'body_markdown' => $postType === 'event' ? 'nullable|string|max:5000' : 'required|string|min:3',
            'visibility'   => $postType === 'event'
                ? 'required|string|in:members,connections'
                : 'sometimes|string|in:members,public,connections',
            'flairs'        => 'sometimes|array',
            'flairs.*'      => [
                'integer',
                Rule::exists('flairs', 'id')->where(function ($query) use ($community) {
                    $community
                        ? $query->where('community_id', $community->id)->orWhereNull('community_id')
                        : $query->whereNull('community_id');
                }),
            ],
            'attachments'   => 'sometimes|array',
            'attachments.*' => 'image|mimes:jpeg,png,gif,jpg|max:5120',
            'removed_media' => 'sometimes|array',
            'removed_media.*' => 'integer',
        ];

        if ($postType === 'event') {
            $rules += [
                'event_type'    => 'required|in:online,in_person',
                'starts_at'     => 'required|date|after:now',
                'ends_at'       => 'nullable|date|after:starts_at',
                'external_link' => ($isOnline ? 'required' : 'nullable') . '|url|max:1024',
                'address'       => ($isOnline ? 'nullable' : 'required') . '|string|max:255',
                'venue'         => 'nullable|string|max:255',
            ];
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        $post->update([
            // Connections-only posts have no community; otherwise persist the chosen one.
            'community_id'  => $isConnectionsOnly ? null : ($validated['community_id'] ?? $post->community_id),
            'title'         => $validated['title'] ?? $post->title,
            'body_markdown' => $validated['body_markdown'] ?? $post->body_markdown,
            'visibility'    => $validated['visibility'] ?? $post->visibility,
        ]);

        if ($postType === 'event' && $post->event) {
            $post->event->update([
                'event_type'    => $validated['event_type'],
                'starts_at'     => $validated['starts_at'],
                'ends_at'       => $validated['ends_at'] ?? null,
                'external_link' => $validated['external_link'] ?? null,
                'address'       => $validated['address'] ?? null,
                'venue'         => $validated['venue'] ?? null,
            ]);
        }

        // Flairs (the composer only sends these for community text/media posts).
        if ($request->has('flairs')) {
            $post->flairs()->sync($validated['flairs'] ?? []);
        }

        // Remove the existing images the user deleted in the composer.
        if (! empty($validated['removed_media'])) {
            foreach ($post->media()->whereIn('id', $validated['removed_media'])->get() as $media) {
                Storage::delete($media->file_path);
                $media->delete();
            }
        }

        // Append any newly attached images.
        if ($request->hasFile('attachments')) {
            $this->postService->addAttachments($post, $request->user(), $request->file('attachments'));
        }

        return redirect()->route('dashboard')->with('success', 'Post updated.');
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
