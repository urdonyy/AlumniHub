<?php

namespace App\Notifications;

use App\Models\CommunityCreationRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CoModeratorInviteNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CommunityCreationRequest $request,
        public User $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'co_moderator_invite',
            'message' => $this->actor->name . ' invited you to co-moderate "' . $this->request->name . '".',
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'request_id' => $this->request->id,
            'url' => route('communities.requests.show', $this->request),
        ];
    }
}
