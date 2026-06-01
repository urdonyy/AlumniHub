<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(BaselineCommunitySeeder::class);
        $this->call(BaselineFlairSeeder::class);
        $this->call(InitialUsersSeeder::class);
    }
}
