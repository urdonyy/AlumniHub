<?php

namespace App\Notifications;

use App\Models\CommunityModeratorTransfer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ModeratorTransferCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CommunityModeratorTransfer $transfer,
        public User $leftUser,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'moderator_transfer_cancelled',
            'message'        => $this->leftUser->name . ' left "' . $this->transfer->community->name . '" so your moderator role transfer was cancelled. You can offer it to another member.',
            'actor_id'       => $this->leftUser->id,
            'actor_name'     => $this->leftUser->name,
            'community_id'   => $this->transfer->community_id,
            'community_name' => $this->transfer->community->name,
            'url'            => route('communities.show', $this->transfer->community_id),
        ];
    }
}
