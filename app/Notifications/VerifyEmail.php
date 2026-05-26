<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends BaseVerifyEmail
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your AlumniHub email address')
            ->view('emails.verify-email', [
                'verificationUrl' => $url,
                'logoUrl'         => asset('images/alumnihub-logo.png'),
                'appName'         => config('app.name', 'AlumniHub'),
            ]);
    }
}
