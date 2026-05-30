<?php

namespace App\Mail;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Post $post,
        public User $actor,
        public User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->actor->name . ' invited you to an event: ' . $this->post->title,
        );
    }

    public function content(): Content
    {
        $event = $this->post->event;

        return new Content(
            view: 'emails.event-invite',
            with: [
                'recipientName' => $this->recipient->name,
                'actorName'     => $this->actor->name,
                'eventName'     => $this->post->title,
                'description'   => $this->post->body_markdown,
                'eventType'     => $event?->event_type,
                'startsAt'      => $event?->starts_at,
                'endsAt'        => $event?->ends_at,
                'externalLink'  => $event?->external_link,
                'address'       => $event?->address,
                'venue'         => $event?->venue,
                'dashboardUrl'  => url('/dashboard'),
                'logoUrl'       => asset('images/alumnihub-logo.png'),
                'appName'       => config('app.name', 'AlumniHub'),
            ],
        );
    }
}
