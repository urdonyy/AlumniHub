<?php

namespace App\Notifications;

use App\Models\CommunityCreationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityCreationPendingReviewNotification extends Notification
{
    use Queueable;

    public function __construct(public CommunityCreationRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'community_creation_pending_review',
            'message' => 'A new community creation request "' . $this->request->name . '" is awaiting your review.',
            'actor_id' => $this->request->requestor_id,
            'actor_name' => $this->request->requestor?->name,
            'request_id' => $this->request->id,
            'url' => route('admin.community-requests.show', $this->request),
        ];
    }
}
