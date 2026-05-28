<?php

namespace App\Services;

use App\Models\Community;
use App\Models\Post;
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
            'body_markdown' => $data['body_markdown'],
            'status' => $data['status'] ?? 'published',
            'visibility' => $data['visibility'] ?? ($community ? 'members' : 'connections'),
            'published_at' => now(),
        ]);

        $post->save();

        // Attach flairs if provided
        if (!empty($data['flairs'])) {
            $post->flairs()->sync($data['flairs']);
        }

        // Handle file uploads
        if (!empty($data['attachments'])) {
            $this->handleAttachments($post, $user, $data['attachments']);
        }

        return $post;
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
