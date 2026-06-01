<?php

namespace App\Notifications;

use App\Models\Community;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityPostRemovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Community $community,
        public Post $post,
        public User $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'community_post_removed',
            'message' => 'Your post in "' . $this->community->name . '" was removed by a moderator.',
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'community_id' => $this->community->id,
            'post_title' => $this->post->title,
            'url' => route('communities.show', $this->community),
        ];
    }
}
