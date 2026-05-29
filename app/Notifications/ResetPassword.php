<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends BaseResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset your AlumniHub password')
            ->view('emails.reset-password', [
                'resetUrl' => $url,
                'logoUrl'  => asset('images/alumnihub-logo.png'),
                'appName'  => config('app.name', 'AlumniHub'),
            ]);
    }
}
