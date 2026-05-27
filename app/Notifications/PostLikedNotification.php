<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostLikedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Post $post,
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
            'type' => 'post_liked',
            'message' => $this->actor->name . ' liked your post.',
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'post_id' => $this->post->id,
            'community_id' => $this->post->community_id,
            'post_title' => $this->post->title,
            'url' => route('communities.posts.open', ['community' => $this->post->community_id, 'post' => $this->post->id]),
        ];
    }
}
