<?php

namespace App\Notifications;

use App\Models\CommunityModeratorTransfer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ModeratorTransferInviteNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CommunityModeratorTransfer $transfer,
        public User $fromUser,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'moderator_transfer_invite',
            'message'         => $this->fromUser->name . ' is offering you the moderator role in "' . $this->transfer->community->name . '".',
            'actor_id'        => $this->fromUser->id,
            'actor_name'      => $this->fromUser->name,
            'community_id'    => $this->transfer->community_id,
            'community_name'  => $this->transfer->community->name,
            'transfer_id'     => $this->transfer->id,
            'url'             => route('communities.show', $this->transfer->community_id),
        ];
    }
}
