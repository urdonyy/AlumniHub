<?php

use App\Models\Community;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;

test('unverified users can browse communities but cannot join', function () {
    $user = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'pending',
    ]);

    $community = Community::create([
        'name' => 'Alumni General',
        'slug' => 'alumni-general',
        'description' => 'General alumni discussions.',
    ]);

    actingAs($user)
        ->get(route('communities.index'))
        ->assertOk()
        ->assertSee('Read-only community access');

    actingAs($user)
        ->get(route('communities.show', $community))
        ->assertOk()
        ->assertSee('This community is read-only until your account is verified.');

    actingAs($user)
        ->post(route('communities.join', $community))
        ->assertForbidden();
});

test('verified users can join and leave communities', function () {
    $user = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $community = Community::create([
        'name' => 'Career Tips',
        'slug' => 'career-tips',
        'description' => 'Career guidance and job leads.',
    ]);

    actingAs($user)
        ->post(route('communities.join', $community))
        ->assertSessionHas('status', 'community-joined');

    assertDatabaseCount('community_user', 1);

    actingAs($user)
        ->delete(route('communities.leave', $community))
        ->assertSessionHas('status', 'community-left');

    assertDatabaseCount('community_user', 0);
});

test('duplicate join requests stay idempotent', function () {
    $user = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    $community = Community::create([
        'name' => 'Jobs Board',
        'slug' => 'jobs-board',
        'description' => 'Shared job opportunities.',
    ]);

    actingAs($user)->post(route('communities.join', $community));
    actingAs($user)->post(route('communities.join', $community));

    expect($user->fresh()->communities)->toHaveCount(1);
    assertDatabaseCount('community_user', 1);
});

test('profile visibility changes with verification state', function () {
    $unverified = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'pending',
    ]);

    $verified = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    actingAs($unverified)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Limited profile visibility')
        ->assertSee('Hidden until verified');

    actingAs($unverified)
        ->get(route('profiles.show', $verified))
        ->assertOk()
        ->assertSee('Additional profile details are hidden until your account is verified.')
        ->assertDontSee($verified->email);

    actingAs($verified)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Full profile visibility');

    actingAs($verified)
        ->get(route('profiles.show', $unverified))
        ->assertOk()
        ->assertSee('Full Contact Details')
        ->assertSee($unverified->email);
});
