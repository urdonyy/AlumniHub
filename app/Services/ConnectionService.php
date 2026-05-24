<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ConnectionService
{
    public function sendInvite(User $sender, User $recipient): array
    {
        if ($sender->id === $recipient->id) {
            throw ValidationException::withMessages([
                'recipient' => 'You cannot send an invite to yourself.',
            ]);
        }

        [$lowId, $highId] = Connection::normalizedPair($sender->id, $recipient->id);

        $connection = Connection::query()
            ->where('user_low_id', $lowId)
            ->where('user_high_id', $highId)
            ->first();

        if (! $connection) {
            $connection = Connection::create([
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'user_low_id' => $lowId,
                'user_high_id' => $highId,
                'status' => Connection::STATUS_PENDING,
                'acted_at' => null,
            ]);

            return ['connection' => $connection, 'result' => 'created'];
        }

        if ($connection->status === Connection::STATUS_ACCEPTED) {
            return ['connection' => $connection, 'result' => 'already_connected'];
        }

        if ($connection->status === Connection::STATUS_PENDING) {
            if ($connection->sender_id === $sender->id) {
                return ['connection' => $connection, 'result' => 'already_pending'];
            }

            return ['connection' => $connection, 'result' => 'invite_received'];
        }

        $connection->update([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'status' => Connection::STATUS_PENDING,
            'acted_at' => null,
        ]);

        return ['connection' => $connection->fresh(), 'result' => 'resent'];
    }

    public function acceptInvite(Connection $connection, User $recipient): Connection
    {
        if ($connection->recipient_id !== $recipient->id) {
            throw ValidationException::withMessages([
                'connection' => 'Only the invite recipient can accept this invite.',
            ]);
        }

        if (! $connection->isPending()) {
            throw ValidationException::withMessages([
                'connection' => 'Only pending invites can be accepted.',
            ]);
        }

        $connection->update([
            'status' => Connection::STATUS_ACCEPTED,
            'acted_at' => now(),
        ]);

        return $connection->fresh();
    }

    public function ignoreInvite(Connection $connection, User $recipient): Connection
    {
        if ($connection->recipient_id !== $recipient->id) {
            throw ValidationException::withMessages([
                'connection' => 'Only the invite recipient can ignore this invite.',
            ]);
        }

        if (! $connection->isPending()) {
            throw ValidationException::withMessages([
                'connection' => 'Only pending invites can be ignored.',
            ]);
        }

        $connection->update([
            'status' => Connection::STATUS_IGNORED,
            'acted_at' => now(),
        ]);

        return $connection->fresh();
    }

    public function pendingReceivedInvites(User $user)
    {
        return Connection::query()
            ->with(['sender'])
            ->where('recipient_id', $user->id)
            ->where('status', Connection::STATUS_PENDING)
            ->latest()
            ->get();
    }

    public function pendingSentInvites(User $user)
    {
        return Connection::query()
            ->with(['recipient'])
            ->where('sender_id', $user->id)
            ->where('status', Connection::STATUS_PENDING)
            ->latest()
            ->get();
    }

    public function acceptedConnections(User $user)
    {
        return Connection::query()
            ->with(['sender', 'recipient'])
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('recipient_id', $user->id);
            })
            ->where('status', Connection::STATUS_ACCEPTED)
            ->latest('acted_at')
            ->get();
    }
}
