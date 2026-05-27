<?php

use App\Models\Community;
use App\Models\CommunityRule;
use App\Models\User;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

test('registration auto joins matching communities', function () {
    $community = Community::create([
        'name' => '2026 ICT Batch',
        'slug' => '2026-ict-batch',
        'description' => 'Students and alumni from the 2026 ICT batch.',
    ]);

    CommunityRule::create([
        'community_id' => $community->id,
        'batch_year' => 2026,
        'program_course' => 'Diploma in Information Communication Technology (DICT)',
    ]);

    $response = post(route('register'), [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'role' => 'alumni',
        'batch_year' => 2026,
        'program_course' => 'Diploma in Information Communication Technology (DICT)',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'jane@example.com')->firstOrFail();

    assertDatabaseHas('community_user', [
        'community_id' => $community->id,
        'user_id' => $user->id,
    ]);

    expect($user->communities)->toHaveCount(1);
    expect($user->communities->first()->is($community))->toBeTrue();
});
