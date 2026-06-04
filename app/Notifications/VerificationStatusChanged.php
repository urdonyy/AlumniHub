<?php

namespace App\Notifications;

use App\Models\VerificationDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public VerificationDocument $verificationDocument) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->verificationDocument->status;
        $statusLabel = strtoupper($status);
        $isApproved = $status === 'approved';

        return (new MailMessage)
            ->subject('Alumni Verification Update')
            ->view('emails.verification-status-changed', [
                'name' => $notifiable->name,
                'statusLabel' => $statusLabel,
                'isApproved' => $isApproved,
                'notes' => $this->verificationDocument->admin_notes,
                'dashboardUrl' => url('/dashboard'),
                'logoUrl' => asset('images/alumnihub-logo.png'),
                'appName' => config('app.name', 'AlumniHub'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $isApproved = $this->verificationDocument->status === 'approved';
        $notes = $this->verificationDocument->admin_notes;

        $message = $isApproved
            ? 'Your account verification was approved. Welcome to AlumniHub!'
            : 'Your account verification was rejected.' . ($notes ? ' Reason: ' . $notes : '');

        return [
            'type' => $isApproved ? 'verification_approved' : 'verification_rejected',
            'message' => $message,
            'notes' => $notes,
            'url' => $isApproved
                ? url('/dashboard')
                : route('profile.edit', ['section' => 'verification-document']),
        ];
    }
}
