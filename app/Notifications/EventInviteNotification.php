<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventInviteNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Post $post,
        public User $actor,
    ) {}

    /**
     * Web (nav bell) notification only. Emails are sent separately via the
     * queued EventInviteMail so the bell works even without a queue worker.
     */
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
            'type' => 'event_invite',
            'message' => $this->actor->name . ' invited you to an event: ' . $this->post->title,
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'post_id' => $this->post->id,
            'community_id' => $this->post->community_id,
            'post_title' => $this->post->title,
            // The notifications index upgrades this to communities.posts.open
            // when both community_id and post_id are present; otherwise the
            // post (a connections-only event) is reachable from the feed.
            'url' => route('dashboard'),
        ];
    }
}
