<?php

namespace App\Policies;

use App\Models\Connection;
use App\Models\User;

class ConnectionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user, User $recipient): bool
    {
        return $user->canSendConnectionRequests() && $user->id !== $recipient->id;
    }

    public function respond(User $user, Connection $connection): bool
    {
        return $connection->recipient_id === $user->id;
    }

    public function withdraw(User $user, Connection $connection): bool
    {
        return $connection->sender_id === $user->id && $connection->isPending();
    }

    public function remove(User $user, Connection $connection): bool
    {
        return ($connection->sender_id === $user->id || $connection->recipient_id === $user->id)
            && $connection->status === Connection::STATUS_ACCEPTED;
    }

    public function view(User $user, Connection $connection): bool
    {
        return $connection->sender_id === $user->id || $connection->recipient_id === $user->id;
    }
}
