<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class InitialUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Site Admin',
                'first_name' => 'Site',
                'last_name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => env('ADMIN_INITIAL_PASSWORD', 'password'),
                'role' => 'admin',
                'account_status' => 'approved',
                'email_verified_at' => now(),
            ]
        );

    }
}
