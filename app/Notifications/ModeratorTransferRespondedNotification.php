<?php

namespace App\Notifications;

use App\Models\CommunityModeratorTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ModeratorTransferRespondedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CommunityModeratorTransfer $transfer,
        public bool $accepted,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $toName = $this->transfer->toUser->name;
        $communityName = $this->transfer->community->name;

        return [
            'type'           => 'moderator_transfer_responded',
            'message'        => $this->accepted
                ? $toName . ' accepted your moderator role transfer in "' . $communityName . '". You can now leave the community.'
                : $toName . ' declined the moderator role transfer in "' . $communityName . '".',
            'actor_id'       => $this->transfer->to_user_id,
            'actor_name'     => $toName,
            'community_id'   => $this->transfer->community_id,
            'community_name' => $communityName,
            'accepted'       => $this->accepted,
            'url'            => route('communities.show', $this->transfer->community_id),
        ];
    }
}
