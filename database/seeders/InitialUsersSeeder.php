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
                'password' => 'password',
                'role' => 'admin',
                'account_status' => 'approved',
            ]
        );

        User::updateOrCreate(
            ['email' => 'alumni@example.com'],
            [
                'name' => 'Test Alumni',
                'first_name' => 'Test',
                'last_name' => 'Alumni',
                'email' => 'alumni@example.com',
                'password' => 'password',
                'role' => 'alumni',
                'account_status' => 'approved',
                'batch_year' => 2020,
                'program_course' => 'Diploma in Computer Engineering Technology (DCET)',
            ]
        );
    }
}
