<?php

namespace App\Notifications;

use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JoinRequestSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CommunityJoinRequest $joinRequest,
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
            'type' => 'join_request_submitted',
            'message' => $this->actor->name . ' wants to join "' . $this->community->name . '".',
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'community_id' => $this->community->id,
            'join_request_id' => $this->joinRequest->id,
            'url' => route('communities.join-requests.index', $this->community),
        ];
    }
}
