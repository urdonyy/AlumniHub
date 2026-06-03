<?php

use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Str;
use function Pest\Laravel\actingAs;

function makeCommunity(string $name, ?string $systemKey = null): Community
{
    return Community::create([
        'name' => $name,
        'slug' => Str::slug($name) . '-' . Str::random(6),
        'system_key' => $systemKey,
        'description' => $name . ' description',
        'created_by' => null,
    ]);
}

test('verified member sees the composer + feed region on a community page', function () {
    $user = User::factory()->create(['account_status' => 'approved']);
    $community = makeCommunity('DICT 3-3 Batch 2023');
    $community->members()->attach($user->id);
    \App\Models\Flair::create([
        'community_id' => null, 'name' => 'Announcement', 'slug' => 'announcement',
        'color' => '#7f1d1d', 'icon' => '📣', 'is_sticky' => false,
    ]);

    actingAs($user)
        ->get(route('communities.show', $community))
        ->assertOk()
        ->assertSee("What's on your mind", false) // composer textarea placeholder
        ->assertSee('Filter by topic');           // feed-region flair filter
});

test('community feed AJAX endpoint returns json html + hasMore', function () {
    $user = User::factory()->create(['account_status' => 'approved']);
    $community = makeCommunity('General Alumni Hub', 'general-alumni-hub');
    $community->members()->attach($user->id);

    actingAs($user)
        ->getJson(route('communities.feed', $community))
        ->assertOk()
        ->assertJsonStructure(['html', 'hasMore']);
});

test('unverified member can VIEW members posts but cannot interact', function () {
    $author = User::factory()->create(['account_status' => 'approved']);
    $unverified = User::factory()->create(['account_status' => 'pending']);
    $community = makeCommunity('General Alumni Hub', 'general-alumni-hub');
    // Everyone is auto-joined to General Hub, including unverified users.
    $community->members()->attach([$author->id, $unverified->id]);

    $membersPost = \App\Models\Post::create([
        'community_id' => $community->id, 'user_id' => $author->id, 'title' => 'Members',
        'body_markdown' => 'hi', 'status' => 'published', 'post_type' => 'text',
        'visibility' => 'members', 'published_at' => now(),
    ]);

    // View: allowed (read-only) for an unverified member.
    actingAs($unverified)
        ->getJson(route('communities.posts.api', ['community' => $community, 'post' => $membersPost]))
        ->assertOk();

    // Interact: blocked by verification.
    actingAs($unverified)
        ->postJson(route('communities.posts.like', ['community' => $community, 'post' => $membersPost]))
        ->assertForbidden();
    actingAs($unverified)
        ->postJson(route('communities.posts.comments.store', ['community' => $community, 'post' => $membersPost]), ['body' => 'hi'])
        ->assertForbidden();
});

test('members modal endpoint returns the alphabetical list to verified users, 403 for unverified', function () {
    $member = User::factory()->create(['account_status' => 'approved', 'name' => 'Bea']);
    $other = User::factory()->create(['account_status' => 'approved', 'name' => 'Ana']);
    $unverified = User::factory()->create(['account_status' => 'pending']);
    $community = makeCommunity('DICT 3-3 Batch 2023');
    $community->members()->attach([$member->id, $other->id, $unverified->id]);

    $res = actingAs($member)
        ->getJson(route('communities.members', $community))
        ->assertOk()
        ->assertJsonStructure(['html', 'count'])
        ->assertJsonPath('count', 3);
    // Alphabetical: Ana appears before Bea in the rendered html.
    $html = $res->json('html');
    expect(strpos($html, 'Ana'))->toBeLessThan(strpos($html, 'Bea'));

    actingAs($unverified)
        ->getJson(route('communities.members', $community))
        ->assertForbidden();
});

test('co-mod candidate list excludes different program/batch connections', function () {
    $requestor = User::factory()->create([
        'account_status' => 'approved', 'batch_year' => 2023, 'program_course' => 'DICT',
    ]);
    $sameBatch = User::factory()->create([
        'account_status' => 'approved', 'batch_year' => 2023, 'program_course' => 'DICT',
    ]);
    $otherBatch = User::factory()->create([
        'account_status' => 'approved', 'batch_year' => 2022, 'program_course' => 'DICT',
    ]);
    $otherProgram = User::factory()->create([
        'account_status' => 'approved', 'batch_year' => 2023, 'program_course' => 'DEET',
    ]);

    foreach ([$sameBatch, $otherBatch, $otherProgram] as $other) {
        \App\Models\Connection::create([
            'sender_id' => $requestor->id, 'recipient_id' => $other->id,
            'user_low_id' => min($requestor->id, $other->id),
            'user_high_id' => max($requestor->id, $other->id),
            'status' => \App\Models\Connection::STATUS_ACCEPTED, 'acted_at' => now(),
        ]);
    }

    actingAs($requestor)
        ->get(route('communities.requests.create'))
        ->assertOk()
        ->assertViewHas('connections', fn ($connections) => $connections->pluck('id')->all() === [$sameBatch->id]);
});
