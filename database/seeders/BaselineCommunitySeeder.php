<?php

namespace Database\Seeders;

use App\Models\Community;
use Illuminate\Database\Seeder;

class BaselineCommunitySeeder extends Seeder
{
    /**
     * @return array<int, array<string, mixed>>
     */
    private function baselineCommunities(): array
    {
        $programs = [
            'Diploma in Civil Engineering Technology (DCvET)' => 'dcvet',
            'Diploma in Computer Engineering Technology (DCET)' => 'dcet',
            'Diploma in Electrical Engineering Technology (DEET)' => 'deet',
            'Diploma in Electronics Engineering Technology (DECET)' => 'decet',
            'Diploma in Information Communication Technology (DICT)' => 'dict',
            'Diploma in Mechanical Engineering Technology (DMET)' => 'dmet',
            'Diploma in Office Management Technology (DOMT)' => 'domt',
            'Diploma in Railway Engineering Technology (DRET)' => 'dret',
        ];

        $communities = [
            [
                'system_key' => 'general-alumni-hub',
                'name' => 'General Alumni Hub',
                'slug' => 'general-alumni-hub',
                'description' => 'Main community for all verified alumni.',
                'rules' => [
                    ['batch_year' => null, 'program_course' => null],
                ],
            ],
        ];

        foreach ($programs as $programCourse => $slugPrefix) {
            $communities[] = [
                'system_key' => 'program-' . $slugPrefix,
                'name' => $programCourse,
                'slug' => $slugPrefix . '-community',
                'description' => 'Official community for ' . $programCourse . '.',
                'rules' => [
                    ['batch_year' => null, 'program_course' => $programCourse],
                ],
            ];
        }

        return $communities;
    }

    public function run(): void
    {
        foreach ($this->baselineCommunities() as $definition) {
            $community = Community::query()->updateOrCreate(
                ['system_key' => $definition['system_key']],
                [
                    'name' => $definition['name'],
                    'slug' => $definition['slug'],
                    'description' => $definition['description'],
                    'is_system' => true,
                    'created_by' => null,
                ]
            );

            $community->rules()->delete();
            $community->rules()->createMany($definition['rules']);
        }
    }
}
