<?php

namespace App\Notifications;

use App\Models\Community;
use App\Models\CommunityCreationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityCreationApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CommunityCreationRequest $request,
        public Community $community,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'community_creation_approved',
            'message' => 'The community "' . $this->community->name . '" was approved.',
            'request_id' => $this->request->id,
            'community_id' => $this->community->id,
            'url' => route('communities.show', $this->community),
        ];
    }
}
