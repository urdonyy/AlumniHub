<?php

namespace Database\Seeders;

use App\Models\Flair;
use Illuminate\Database\Seeder;

class BaselineFlairSeeder extends Seeder
{
    public function run(): void
    {
        $systemFlairs = [
            [
                'name'       => 'Event',
                'slug'       => 'event',
                'color'      => '#4F46E5',
                'icon'       => '📅',
                'is_sticky'  => false,
                'is_system'  => true,
            ],
        ];

        foreach ($systemFlairs as $data) {
            Flair::updateOrCreate(
                ['community_id' => null, 'slug' => $data['slug']],
                $data,
            );
        }
    }
}
