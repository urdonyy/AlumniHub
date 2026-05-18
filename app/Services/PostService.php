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
    public function createPost(Community $community, User $user, array $data): Post
    {
        // Create the post
        $post = new Post([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'title' => $data['title'] ?? null,
            'body_markdown' => $data['body_markdown'],
            'status' => 'published',
            'visibility' => 'members',
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
            'body_markdown' => $data['body_markdown'],
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

            // Store file in storage/app/public/posts/{community_id}/{post_id}/
            $path = $file->store(
                "posts/{$post->community_id}/{$post->id}",
                'public'
            );

            // Extract file metadata
            $fileSize = $file->getSize();
            $fileType = $file->getClientMimeType();

            // Get image dimensions if applicable
            $meta = [];
            if (str_starts_with($fileType, 'image/')) {
                $imagePath = Storage::disk('public')->path($path);
                $size = getimagesize($imagePath);
                if ($size) {
                    $meta['width'] = $size[0];
                    $meta['height'] = $size[1];
                }
            }

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
