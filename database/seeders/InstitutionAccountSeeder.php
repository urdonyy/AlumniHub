<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstitutionAccountSeeder extends Seeder
{
    /**
     * Seed the single PUP-ITECH Official institution account (role superadmin):
     * pre-verified, onboarding complete (no registration), and joined to every
     * system community (General Alumni Hub + program communities) so it acts as a
     * member/moderator there. It deliberately does NOT join batch communities —
     * those belong to the students/alumni of that batch. Idempotent.
     */
    public function run(): void
    {
        $institution = User::query()->updateOrCreate(
            ['role' => 'superadmin'],
            [
                'name' => 'PUP-ITECH Official',
                'first_name' => 'PUP-ITECH',
                'last_name' => 'Official',
                'email' => 'official@pup-itech.edu.ph',
                'account_status' => 'approved',
                'email_verified_at' => now(),
                'profile_setup_completed_at' => now(),
                'password' => Hash::make(Str::random(40)),
            ]
        );

        // Join only the system communities (General Alumni Hub + program
        // communities). Batch communities are left to their own batch members.
        $institution->communities()->syncWithoutDetaching(
            Community::query()->where('is_system', true)->pluck('id')->all()
        );

        // Self-heal: drop any batch (non-system) memberships the institution may
        // have picked up before this policy, so a reseed corrects existing drift.
        $institution->communities()->detach(
            Community::query()->where('is_system', false)->pluck('id')->all()
        );
    }
}
