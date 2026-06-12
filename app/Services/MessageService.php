<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class MessageService
{
    /**
     * Persist a message from $sender to $recipient and broadcast it in real time.
     */
    public function send(User $sender, User $recipient, string $body): Message
    {
        $conversation = Conversation::betweenUsers($sender, $recipient);

        $message = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body'      => $body,
        ]);

        $conversation->update(['last_message_at' => $message->created_at]);

        $message->load('sender');

        // Synchronous broadcast (ShouldBroadcastNow) — no queue worker on Heroku.
        broadcast(new MessageSent($message, $recipient->id))->toOthers();

        return $message;
    }

    /**
     * Mark every message the other party sent in this conversation as read.
     */
    public function markConversationRead(Conversation $conversation, User $reader): void
    {
        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $reader->id)
            ->update(['read_at' => now()]);
    }
}
