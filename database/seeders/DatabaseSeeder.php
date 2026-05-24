<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(BaselineCommunitySeeder::class);

        // Create initial admin and test alumni user
        $this->call(InitialUsersSeeder::class);

        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                // provide a default password so the row can be created
                'password' => 'password',
            ]
        );

        // Seed posts and flairs after baseline communities and a test user exist
        $this->call(\Database\Seeders\PostSeeder::class);
    }
}
