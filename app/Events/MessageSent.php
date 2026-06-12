<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast a newly created direct message in real time.
 *
 * Implements ShouldBroadcastNow (not ShouldBroadcast) so the broadcast is sent
 * synchronously during the request — the app runs on Heroku with no queue worker
 * dyno, mirroring the synchronous Mail::send() approach in EventInviteService.
 */
class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
        public int $recipientId,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            // Both participants subscribe while viewing the thread → live append.
            new PrivateChannel("conversation.{$this->message->conversation_id}"),
            // The recipient subscribes app-wide → nav badge + toast anywhere.
            new PrivateChannel("user.{$this->recipientId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $sender = $this->message->sender;

        return [
            'id'                => $this->message->id,
            'conversation_id'   => $this->message->conversation_id,
            'sender_id'         => $this->message->sender_id,
            'sender_name'       => $sender?->name,
            'sender_avatar_url' => $sender?->profileAvatarUrl(),
            'body'              => $this->message->body,
            'created_at_human'  => $this->message->created_at->diffForHumans(),
        ];
    }
}
