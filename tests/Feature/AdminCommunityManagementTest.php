<?php

use App\Models\Community;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('admin can create update and delete communities', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'approved',
    ]);

    actingAs($admin)
        ->post(route('admin.communities.store'), [
            'name' => 'Batch 2025',
            'slug' => 'batch-2025',
            'description' => 'Auto-assigned alumni for batch 2025.',
            'rule_batch_year' => 2025,
            'rule_program_course' => 'Diploma in Information Communication Technology (DICT)',
        ])
        ->assertSessionHas('status', 'community-created');

    $community = Community::where('slug', 'batch-2025')->firstOrFail();

    assertDatabaseHas('communities', [
        'id' => $community->id,
        'name' => 'Batch 2025',
    ]);

    assertDatabaseHas('community_rules', [
        'community_id' => $community->id,
        'batch_year' => 2025,
        'program_course' => 'Diploma in Information Communication Technology (DICT)',
    ]);

    actingAs($admin)
        ->patch(route('admin.communities.update', $community), [
            'name' => 'Batch 2025 Updated',
            'slug' => 'batch-2025',
            'description' => 'Updated description',
        ])
        ->assertSessionHas('status', 'community-updated');

    assertDatabaseHas('communities', [
        'id' => $community->id,
        'name' => 'Batch 2025 Updated',
        'description' => 'Updated description',
    ]);

    actingAs($admin)
        ->delete(route('admin.communities.destroy', $community))
        ->assertSessionHas('status', 'community-deleted');

    assertDatabaseMissing('communities', [
        'id' => $community->id,
    ]);
});

test('non admin cannot access community management routes', function () {
    $user = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);

    actingAs($user)
        ->get(route('admin.communities.index'))
        ->assertForbidden();
});

test('admin cannot delete system communities', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'approved',
    ]);

    $systemCommunity = Community::create([
        'name' => 'General Alumni Hub',
        'slug' => 'general-alumni-hub-test',
        'is_system' => true,
        'system_key' => 'general-alumni-hub-test',
    ]);

    actingAs($admin)
        ->delete(route('admin.communities.destroy', $systemCommunity))
        ->assertSessionHas('status', 'community-delete-blocked-system');

    assertDatabaseHas('communities', [
        'id' => $systemCommunity->id,
    ]);
});
