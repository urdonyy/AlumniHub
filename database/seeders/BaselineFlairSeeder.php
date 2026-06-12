<?php

namespace Database\Seeders;

use App\Models\Flair;
use Illuminate\Database\Seeder;

class BaselineFlairSeeder extends Seeder
{
    public function run(): void
    {
        $globalFlairs = [
            ['name' => 'Event',                   'slug' => 'event',                 'color' => '#4F46E5', 'icon' => '📅',   'is_system' => true],
            ['name' => 'Announcement',             'slug' => 'announcement',          'color' => '#6366F1', 'icon' => '📢',   'is_system' => false],
            ['name' => 'Event / Webinar Stories',  'slug' => 'event-webinar',         'color' => '#10B981', 'icon' => '🗓️',  'is_system' => false],
            ['name' => 'Balik Sinta Stories',      'slug' => 'balik-sinta-stories',   'color' => '#F59E0B', 'icon' => '📖',   'is_system' => false],
            ['name' => 'Alumni Spotlight',         'slug' => 'alumni-spotlight',      'color' => '#F97316', 'icon' => '✨',   'is_system' => false],
            ['name' => 'Career Tips',              'slug' => 'career-tips',           'color' => '#FB7185', 'icon' => '🎯',   'is_system' => false],
            ['name' => 'Job Referral',             'slug' => 'job-referral',          'color' => '#0EA5E9', 'icon' => '💼',   'is_system' => false],
            ['name' => 'Mentorship',               'slug' => 'mentorship',            'color' => '#8B5CF6', 'icon' => '🤝',   'is_system' => false],
            ['name' => 'Advice',                   'slug' => 'advice',                'color' => '#EF4444', 'icon' => '💡',   'is_system' => false],
            ['name' => 'Resources',                'slug' => 'resources',             'color' => '#06B6D4', 'icon' => '📚',   'is_system' => false],
            ['name' => 'Research & Publications',  'slug' => 'research-publications', 'color' => '#0EA5E9', 'icon' => '🔬',   'is_system' => false],
            ['name' => 'Volunteer Opportunity',    'slug' => 'volunteer-opportunity', 'color' => '#84CC16', 'icon' => '🙋',   'is_system' => false],
            ['name' => 'Showcase',                 'slug' => 'showcase',              'color' => '#06B6D4', 'icon' => '🏆',   'is_system' => false],
            ['name' => 'Question',                 'slug' => 'question',              'color' => '#F97316', 'icon' => '❓',   'is_system' => false],
            ['name' => 'School News',              'slug' => 'school-news',           'color' => '#334155', 'icon' => '🏫',   'is_system' => false],
        ];

        foreach ($globalFlairs as $data) {
            Flair::updateOrCreate(
                ['community_id' => null, 'slug' => $data['slug']],
                $data,
            );
        }
    }
}
