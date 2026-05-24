<?php

use App\Models\Community;
use App\Models\Connection;
use App\Models\Flair;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

function createCommunity(string $name): Community
{
    return Community::create([
        'name' => $name,
        'slug' => Str::slug($name) . '-' . Str::random(6),
        'description' => $name . ' description',
        'created_by' => null,
    ]);
}

test('quick composer requires a title', function () {
    $user = User::factory()->create();
    $community = createCommunity('General Alumni Hub');

    $community->members()->attach($user->id);

    actingAs($user)
        ->from('/dashboard')
        ->post(route('posts.quick-store'), [
            'community_id' => $community->id,
            'body_markdown' => 'Body only',
            'visibility' => 'public',
        ])
        ->assertSessionHasErrors('title');
});

test('quick composer saves visibility and selected flair', function () {
    $user = User::factory()->create();
    $community = createCommunity('General Alumni Hub');

    $community->members()->attach($user->id);

    $flair = Flair::create([
        'community_id' => $community->id,
        'name' => 'Announcement',
        'slug' => 'announcement',
        'color' => '#ff0000',
        'icon' => '📣',
        'is_sticky' => false,
    ]);

    actingAs($user)
        ->post(route('posts.quick-store'), [
            'community_id' => $community->id,
            'title' => 'Composer Test',
            'body_markdown' => 'Hello feed',
            'visibility' => 'connections',
            'flairs' => [$flair->id],
        ])
        ->assertRedirect(route('communities.posts.index', $community));

    $post = Post::query()->where('title', 'Composer Test')->firstOrFail();

    assertDatabaseHas('posts', [
        'id' => $post->id,
        'community_id' => $community->id,
        'user_id' => $user->id,
        'visibility' => 'connections',
    ]);

    assertDatabaseHas('flair_post', [
        'post_id' => $post->id,
        'flair_id' => $flair->id,
    ]);
});

test('dashboard feed only shows posts the viewer can access', function () {
    $viewer = User::factory()->create();
    $publicAuthor = User::factory()->create();
    $communityAuthor = User::factory()->create();
    $connectionAuthor = User::factory()->create();
    $blockedAuthor = User::factory()->create();

    $community = createCommunity('General Alumni Hub');
    $community->members()->attach($viewer->id);
    $community->members()->attach($communityAuthor->id);

    Connection::create([
        'sender_id' => $viewer->id,
        'recipient_id' => $connectionAuthor->id,
        'user_low_id' => min($viewer->id, $connectionAuthor->id),
        'user_high_id' => max($viewer->id, $connectionAuthor->id),
        'status' => Connection::STATUS_ACCEPTED,
        'acted_at' => now(),
    ]);

    Post::create([
        'community_id' => $community->id,
        'user_id' => $publicAuthor->id,
        'title' => 'Public Post',
        'body_markdown' => 'Public body',
        'status' => 'published',
        'visibility' => 'public',
        'published_at' => now(),
    ]);

    Post::create([
        'community_id' => $community->id,
        'user_id' => $communityAuthor->id,
        'title' => 'Community Post',
        'body_markdown' => 'Community body',
        'status' => 'published',
        'visibility' => 'members',
        'published_at' => now(),
    ]);

    Post::create([
        'community_id' => $community->id,
        'user_id' => $connectionAuthor->id,
        'title' => 'Connections Post',
        'body_markdown' => 'Connections body',
        'status' => 'published',
        'visibility' => 'connections',
        'published_at' => now(),
    ]);

    Post::create([
        'community_id' => $community->id,
        'user_id' => $blockedAuthor->id,
        'title' => 'Blocked Post',
        'body_markdown' => 'Blocked body',
        'status' => 'published',
        'visibility' => 'connections',
        'published_at' => now(),
    ]);

    actingAs($viewer)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Public Post')
        ->assertSee('Community Post')
        ->assertSee('Connections Post')
        ->assertDontSee('Blocked Post');
});
