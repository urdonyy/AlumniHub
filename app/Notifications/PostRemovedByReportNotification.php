<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\PostReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostRemovedByReportNotification extends Notification
{
    use Queueable;

    /**
     * @param  Post    $post     The (already removed) post — used only for its title.
     * @param  string  $reason   A PostReport::REASONS key chosen by the admin.
     * @param  ?string $note     Optional extra context from the admin.
     */
    public function __construct(
        public Post $post,
        public string $reason,
        public ?string $note = null,
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
        $label = PostReport::reasonLabel($this->reason);

        $message = 'Your post was removed for ' . $label . '. '
            . 'Repeated violations of our community guidelines may lead to your account being suspended.';

        if ($this->note) {
            $message .= ' Note from the moderator: ' . $this->note;
        }

        return [
            'type' => 'post_removed_violation',
            'message' => $message,
            'reason' => $this->reason,
            'reason_label' => $label,
            'note' => $this->note,
            'post_title' => $this->post->title,
            'url' => route('dashboard'),
        ];
    }
}
