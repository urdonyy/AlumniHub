<?php

namespace App\Notifications;

use App\Models\Community;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityMemberRemovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Community $community,
        public User $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'community_member_removed',
            'message' => 'You were removed from "' . $this->community->name . '" by ' . $this->actor->name . '.',
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'community_id' => $this->community->id,
            'url' => route('communities.show', $this->community),
        ];
    }
}
