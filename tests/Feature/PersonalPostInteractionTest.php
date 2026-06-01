<?php

use App\Models\Connection;
use App\Models\Post;
use App\Models\User;
use function Pest\Laravel\actingAs;

test('connected viewer can open, like, and comment on a community-less connections post', function () {
    $author = User::factory()->create(['account_status' => 'approved']);
    $viewer = User::factory()->create(['account_status' => 'approved']);

    // Make them connected (accepted).
    Connection::create([
        'sender_id' => $author->id,
        'recipient_id' => $viewer->id,
        'user_low_id' => min($author->id, $viewer->id),
        'user_high_id' => max($author->id, $viewer->id),
        'status' => Connection::STATUS_ACCEPTED,
        'acted_at' => now(),
    ]);

    // Community-less connections post (what an event/text post to "Connections" creates).
    $post = Post::create([
        'community_id' => null,
        'user_id' => $author->id,
        'title' => 'Connections Event',
        'body_markdown' => 'See you there',
        'status' => 'published',
        'post_type' => 'event',
        'visibility' => 'connections',
        'published_at' => now(),
    ]);

    // 1) Open (detail API) — community must be null, not a 500; not yet liked.
    actingAs($viewer)
        ->getJson(route('posts.api', ['post' => $post]))
        ->assertOk()
        ->assertJsonPath('post.id', $post->id)
        ->assertJsonPath('post.community', null)
        ->assertJsonPath('post.post_type', 'event')
        ->assertJsonPath('post.is_liked', false);

    // 2) Like
    actingAs($viewer)
        ->postJson(route('posts.like', ['post' => $post]))
        ->assertOk()
        ->assertJsonPath('liked', true)
        ->assertJsonPath('like_count', 1);

    // Re-opening the modal must report the post as already liked (so the heart
    // shows red instead of resetting to grey).
    actingAs($viewer)
        ->getJson(route('posts.api', ['post' => $post]))
        ->assertOk()
        ->assertJsonPath('post.is_liked', true);

    // 3) Comment
    actingAs($viewer)
        ->postJson(route('posts.comments.store', ['post' => $post]), ['body' => 'Nice!'])
        ->assertOk()
        ->assertJsonPath('success', true);

    // 4) Read comments back
    actingAs($viewer)
        ->getJson(route('posts.comments.index', ['post' => $post]))
        ->assertOk();

    expect($post->fresh()->like_count)->toBe(1);
    expect($post->allComments()->count())->toBe(1);
});

test('notification open link redirects to dashboard with the modal payload', function () {
    $author = User::factory()->create(['account_status' => 'approved']);
    $viewer = User::factory()->create(['account_status' => 'approved']);

    Connection::create([
        'sender_id' => $author->id,
        'recipient_id' => $viewer->id,
        'user_low_id' => min($author->id, $viewer->id),
        'user_high_id' => max($author->id, $viewer->id),
        'status' => Connection::STATUS_ACCEPTED,
        'acted_at' => now(),
    ]);

    $post = Post::create([
        'community_id' => null,
        'user_id' => $author->id,
        'title' => 'Open me',
        'body_markdown' => 'hi',
        'status' => 'published',
        'post_type' => 'event',
        'visibility' => 'connections',
        'published_at' => now(),
    ]);

    actingAs($viewer)
        ->get(route('posts.open', ['post' => $post]))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('openPostModal', fn ($payload) => $payload['postId'] === $post->id
            && str_contains($payload['apiUrl'], "/posts/{$post->id}/api"));
});

test('author can comment on their own community-less connections post', function () {
    $author = User::factory()->create(['account_status' => 'approved']);

    $post = Post::create([
        'community_id' => null,
        'user_id' => $author->id,
        'title' => 'My event',
        'body_markdown' => 'come',
        'status' => 'published',
        'post_type' => 'event',
        'visibility' => 'connections',
        'published_at' => now(),
    ]);

    // Author is not "connected to themselves", but must still be able to comment.
    actingAs($author)
        ->postJson(route('posts.comments.store', ['post' => $post]), ['body' => 'first!'])
        ->assertOk()
        ->assertJsonPath('success', true);
});

test('non-connected verified user is forbidden (not a 500) on a connections post', function () {
    $author = User::factory()->create(['account_status' => 'approved']);
    $stranger = User::factory()->create(['account_status' => 'approved']);

    $post = Post::create([
        'community_id' => null,
        'user_id' => $author->id,
        'title' => 'Private-ish',
        'body_markdown' => 'hello',
        'status' => 'published',
        'post_type' => 'text',
        'visibility' => 'connections',
        'published_at' => now(),
    ]);

    actingAs($stranger)
        ->getJson(route('posts.api', ['post' => $post]))
        ->assertForbidden();
});
