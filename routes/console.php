<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('make:admin {email}', function (string $email) {
    $user = User::query()->where('email', $email)->first();

    if (! $user) {
        $this->error("User not found for email: {$email}");

        return;
    }

    $user->update([
        'role' => 'admin',
        'account_status' => 'approved',
    ]);

    $this->info("{$user->email} is now an admin.");
})->purpose('Promote an existing user to admin role');
