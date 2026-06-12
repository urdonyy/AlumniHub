<?php

namespace App\Notifications;

use App\Models\Community;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JoinRequestAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(public Community $community) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'join_request_accepted',
            'message' => 'Your request to join "' . $this->community->name . '" was accepted.',
            'community_id' => $this->community->id,
            'url' => route('communities.show', $this->community),
        ];
    }
}
