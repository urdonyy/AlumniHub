<?php

namespace App\Notifications;

use App\Models\CommunityCreationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommunityCreationRejectedNotification extends Notification
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
            'type' => 'community_creation_rejected',
            'message' => 'Your community creation request "' . $this->request->name . '" was rejected.',
            'request_id' => $this->request->id,
            'admin_note' => $this->request->admin_note,
            'url' => route('communities.requests.show', $this->request),
        ];
    }
}
