<?php

namespace App\Notifications;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConnectionAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Connection $connectionModel,
        public User $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'connection_accepted',
            'message' => $this->actor->name . ' accepted your connection invite.',
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'connection_id' => $this->connectionModel->id,
            'url' => route('profiles.show', $this->actor->id),
        ];
    }
}
