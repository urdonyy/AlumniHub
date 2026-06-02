<?php

namespace App\Services;

use App\Mail\EventInviteMail;
use App\Models\Post;
use App\Models\User;
use App\Notifications\EventInviteNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class EventInviteService
{
    /**
     * Auto-invite the audience of an event post.
     *
     * Sends an immediate web (nav-bell) notification and a best-effort email to
     * every member of the post's audience. The audience is the set of
     * people who can see the post: the author's connections (connections-only
     * events) or the community's members (community events). Public events do
     * not support auto-invite.
     */
    public function dispatch(Post $post, User $author): void
    {
        // Only verified accounts may view the post (others get a 403), so don't
        // notify or email unverified users about events they can't open.
        $recipients = $this->resolveAudience($post, $author)
            ->filter(fn (User $recipient) => $recipient->isVerified())
            ->values();

        if ($recipients->isEmpty()) {
            $this->markInvited($post);
            return;
        }

        // Web notification — synchronous so the nav bell works regardless of
        // whether a queue worker is running.
        Notification::send($recipients, new EventInviteNotification($post, $author));

        // Email — sent synchronously and best-effort; a single failure must not
        // break the post-creation flow.
        foreach ($recipients as $recipient) {
            if (empty($recipient->email)) {
                continue;
            }

            try {
                Mail::to($recipient->email)->send(new EventInviteMail($post, $author, $recipient));
            } catch (\Throwable $e) {
                Log::warning('Failed to send event invite email', [
                    'post_id' => $post->id,
                    'recipient_id' => $recipient->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->markInvited($post);
    }

    /**
     * Resolve the audience for an event post (excluding the author).
     *
     * @return Collection<int, User>
     */
    protected function resolveAudience(Post $post, User $author): Collection
    {
        if ($post->visibility === 'connections') {
            $connectedUserIds = $author->connections()
                ->get()
                ->map(fn ($connection) => $connection->sender_id === $author->id
                    ? $connection->recipient_id
                    : $connection->sender_id)
                ->unique()
                ->values();

            return User::query()
                ->whereIn('id', $connectedUserIds)
                ->get();
        }

        if ($post->visibility === 'members' && $post->community) {
            return $post->community->members()
                ->where('users.id', '!=', $author->id)
                ->get();
        }

        return collect();
    }

    protected function markInvited(Post $post): void
    {
        $post->event?->update(['auto_invited' => true]);
    }
}
