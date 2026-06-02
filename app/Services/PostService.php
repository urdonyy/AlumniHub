<?php

namespace App\Services;

use App\Models\Community;
use App\Models\Flair;
use App\Models\Post;
use App\Models\PostEvent;
use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class PostService
{
    /**
     * Create a new post with attachments and flairs.
     */
    public function createPost(?Community $community, User $user, array $data): Post
    {
        // Create the post
        $post = new Post([
            'community_id' => $community?->id,
            'user_id' => $user->id,
            'title' => $data['title'] ?? null,
            'body_markdown' => $data['body_markdown'] ?? null,
            'status' => $data['status'] ?? 'published',
            'post_type' => $data['post_type'] ?? 'text',
            'visibility' => $data['visibility'] ?? ($community ? 'members' : 'connections'),
            'published_at' => now(),
        ]);

        $post->save();

        // Users may only assign selectable flairs; strip any system flairs
        // (e.g. "Event") that were submitted directly.
        $flairIds = Flair::whereIn('id', $data['flairs'] ?? [])
            ->selectable()
            ->pluck('id')
            ->all();

        // Persist event details for event posts. Event-type posts are auto-tagged
        // with the system "Event" flair so they surface under that topic filter.
        if (($data['post_type'] ?? null) === 'event') {
            $this->createEvent($post, $data);

            if ($eventFlairId = $this->eventFlairId()) {
                $flairIds[] = $eventFlairId;
            }
        }

        if (!empty($flairIds)) {
            $post->flairs()->sync(array_values(array_unique($flairIds)));
        }

        // Handle file uploads
        if (!empty($data['attachments'])) {
            $this->handleAttachments($post, $user, $data['attachments']);
        }

        return $post;
    }

    /**
     * Create the related event record for an event post.
     */
    protected function createEvent(Post $post, array $data): PostEvent
    {
        return PostEvent::create([
            'post_id' => $post->id,
            'event_type' => $data['event_type'] ?? 'online',
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'external_link' => $data['external_link'] ?? null,
            'address' => $data['address'] ?? null,
            'venue' => $data['venue'] ?? null,
        ]);
    }

    /**
     * Resolve the id of the global system "Event" flair, if it exists.
     */
    protected function eventFlairId(): ?int
    {
        return Flair::query()
            ->whereNull('community_id')
            ->where('slug', 'event')
            ->value('id');
    }

    /**
     * Update an existing post.
     */
    public function updatePost(Post $post, array $data): Post
    {
        $post->update([
            'title' => $data['title'] ?? $post->title,
            'body_markdown' => $data['body_markdown'] ?? $post->body_markdown,
            'visibility' => $data['visibility'] ?? $post->visibility,
            'status' => $data['status'] ?? $post->status,
        ]);

        // Update flairs if provided
        if (isset($data['flairs'])) {
            $post->flairs()->sync($data['flairs']);
        }

        // Handle new file uploads
        if (!empty($data['attachments'])) {
            $this->handleAttachments($post, $post->user, $data['attachments']);
        }

        return $post;
    }

    /**
     * Publicly append new file attachments to an existing post (used when editing).
     */
    public function addAttachments(Post $post, User $user, array $files): void
    {
        $this->handleAttachments($post, $user, $files);
    }

    /**
     * Handle file attachments for a post.
     */
    protected function handleAttachments(Post $post, User $user, array $files): void
    {
        $order = $post->media()->max('order') ?? 0;

        foreach ($files as $file) {
            $order++;

            // Store file in storage/app/public/posts/{community_id}/{post_id}/ or posts/personal/{post_id}/
            $storagePath = $post->community_id
                ? "posts/{$post->community_id}/{$post->id}"
                : "posts/personal/{$post->id}";

            // Extract file metadata before storing
            $fileSize = $file->getSize();
            $fileType = $file->getClientMimeType();

            // Get image dimensions from temp file before upload
            $meta = [];
            if (str_starts_with($fileType, 'image/')) {
                $size = getimagesize($file->getRealPath());
                if ($size) {
                    $meta['width'] = $size[0];
                    $meta['height'] = $size[1];
                }
            }

            $path = $file->store($storagePath);

            // Create media record
            PostMedia::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'file_path' => $path,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'meta' => $meta,
                'order' => $order,
            ]);
        }
    }

    /**
     * Assign flair to a post.
     */
    public function assignFlair(Post $post, int $flairId): void
    {
        $post->flairs()->attach($flairId);
    }

    /**
     * Remove flair from a post.
     */
    public function removeFlair(Post $post, int $flairId): void
    {
        $post->flairs()->detach($flairId);
    }

    /**
     * Pin a post.
     */
    public function pinPost(Post $post): void
    {
        $post->update(['pinned' => true]);
    }

    /**
     * Unpin a post.
     */
    public function unpinPost(Post $post): void
    {
        $post->update(['pinned' => false]);
    }

    /**
     * Hide a post (moderator action).
     */
    public function hidePost(Post $post): void
    {
        $post->update(['status' => 'hidden']);
    }

    /**
     * Show a post (moderator action).
     */
    public function showPost(Post $post): void
    {
        $post->update(['status' => 'published']);
    }
}
