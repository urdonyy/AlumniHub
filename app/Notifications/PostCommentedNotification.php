<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostCommentedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Post $post,
        public Comment $comment,
        public User $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_commented',
            'message' => $this->actor->name . ' commented on your post.',
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'post_id' => $this->post->id,
            'community_id' => $this->post->community_id,
            'post_title' => $this->post->title,
            'comment_id' => $this->comment->id,
            'comment_preview' => mb_substr((string) $this->comment->body, 0, 140),
            'url' => $this->post->community_id
                ? route('communities.posts.open', ['community' => $this->post->community_id, 'post' => $this->post->id])
                : route('dashboard'),
        ];
    }
}
