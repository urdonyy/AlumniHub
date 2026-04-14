<?php

use App\Models\Community;
use Database\Seeders\BaselineCommunitySeeder;
use function Pest\Laravel\seed;

test('baseline community seeder is idempotent', function () {
    seed(BaselineCommunitySeeder::class);
    seed(BaselineCommunitySeeder::class);

    expect(Community::query()->where('is_system', true)->count())->toBe(9);
    expect(Community::query()->whereNotNull('system_key')->count())->toBe(9);
    expect(Community::query()->count())->toBe(9);

    $generalHub = Community::query()->where('system_key', 'general-alumni-hub')->first();

    expect($generalHub)->not->toBeNull();
    expect($generalHub->rules()->count())->toBe(1);
    expect($generalHub->rules()->first()->batch_year)->toBeNull();
    expect($generalHub->rules()->first()->program_course)->toBeNull();
});
