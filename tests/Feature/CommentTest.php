<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

test('user can post a comment', function () {
    $author = createVerifiedUser();
    $commenter = createVerifiedUser();
    $community = createCommunityWithMembers($author, $commenter);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create([
            'visibility' => 'members',
            'status' => 'published',
        ]);

    actingAs($commenter)
        ->postJson(
            route('communities.posts.comments.store', [
                'community' => $community,
                'post' => $post,
            ]),
            [
                'body' => 'This is a great post!',
            ]
        )
        ->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'comment' => [
                'id',
                'user',
                'body',
                'created_at',
                'parent_comment_id',
                'replies',
            ],
        ]);

    expect(Comment::count())->toBe(1);
    expect(Comment::first()->body)->toBe('This is a great post!');
});

test('unverified user cannot post comment', function () {
    $author = createVerifiedUser();
    $commenter = createVerifiedUser();
    $unverified = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'pending',
    ]);

    $community = createCommunityWithMembers($author, $commenter, $unverified);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create([
            'visibility' => 'members',
            'status' => 'published',
        ]);

    actingAs($unverified)
        ->postJson(
            route('communities.posts.comments.store', [
                'community' => $community,
                'post' => $post,
            ]),
            [
                'body' => 'This is a comment',
            ]
        )
        ->assertForbidden();
});

test('user can reply to comment with depth limit', function () {
    $author = createVerifiedUser();
    $commenter = createVerifiedUser();
    $community = createCommunityWithMembers($author, $commenter);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['visibility' => 'members', 'status' => 'published']);

    // Create parent comment
    $parent = Comment::create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'body' => 'Parent comment',
        'parent_comment_id' => null,
    ]);

    // Create reply to parent
    $reply = Comment::create([
        'post_id' => $post->id,
        'user_id' => $commenter->id,
        'body' => 'Reply to parent',
        'parent_comment_id' => $parent->id,
    ]);

    // Try to reply to reply (depth 2, should succeed)
    actingAs($commenter)
        ->postJson(
            route('communities.posts.comments.store', [
                'community' => $community,
                'post' => $post,
            ]),
            [
                'body' => 'Reply to reply',
                'parent_comment_id' => $reply->id,
            ]
        )
        ->assertSuccessful();
});

test('user cannot reply beyond depth limit', function () {
    $author = createVerifiedUser();
    $commenter = createVerifiedUser();
    $community = createCommunityWithMembers($author, $commenter);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['visibility' => 'members', 'status' => 'published']);

    // Create a chain at max depth
    $level0 = Comment::create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'body' => 'Level 0',
        'parent_comment_id' => null,
    ]);

    $level1 = Comment::create([
        'post_id' => $post->id,
        'user_id' => $commenter->id,
        'body' => 'Level 1',
        'parent_comment_id' => $level0->id,
    ]);

    $level2 = Comment::create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'body' => 'Level 2',
        'parent_comment_id' => $level1->id,
    ]);

    // Try to reply to level 2 (should fail)
    actingAs($commenter)
        ->postJson(
            route('communities.posts.comments.store', [
                'community' => $community,
                'post' => $post,
            ]),
            [
                'body' => 'Level 3 - should fail',
                'parent_comment_id' => $level2->id,
            ]
        )
        ->assertStatus(422)
        ->assertJsonFragment([
            'error' => 'Cannot reply to this comment (max 3 levels deep)',
        ]);
});

test('user can view comments', function () {
    $author = createVerifiedUser();
    $commenter = createVerifiedUser();
    $community = createCommunityWithMembers($author, $commenter);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['visibility' => 'members', 'status' => 'published']);

    Comment::create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'body' => 'First comment',
    ]);

    Comment::create([
        'post_id' => $post->id,
        'user_id' => $commenter->id,
        'body' => 'Second comment',
    ]);

    actingAs($commenter)
        ->getJson(
            route('communities.posts.comments.index', [
                'community' => $community,
                'post' => $post,
            ])
        )
        ->assertSuccessful()
        ->assertJsonCount(2, 'comments');
});

test('user can delete own comment', function () {
    $author = createVerifiedUser();
    $commenter = createVerifiedUser();
    $community = createCommunityWithMembers($author, $commenter);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['visibility' => 'members', 'status' => 'published']);

    $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $commenter->id,
        'body' => 'My comment',
    ]);

    actingAs($commenter)
        ->deleteJson(
            route('communities.posts.comments.destroy', [
                'community' => $community,
                'post' => $post,
                'comment' => $comment,
            ])
        )
        ->assertSuccessful();

    expect(Comment::count())->toBe(0);
});

test('user cannot delete others comment', function () {
    $author = createVerifiedUser();
    $commenter = createVerifiedUser();
    $community = createCommunityWithMembers($author, $commenter);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['visibility' => 'members', 'status' => 'published']);

    $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'body' => 'Author\'s comment',
    ]);

    actingAs($commenter)
        ->deleteJson(
            route('communities.posts.comments.destroy', [
                'community' => $community,
                'post' => $post,
                'comment' => $comment,
            ])
        )
        ->assertForbidden();

    expect(Comment::count())->toBe(1);
});

test('post author can delete any comment', function () {
    $author = createVerifiedUser();
    $commenter = createVerifiedUser();
    $community = createCommunityWithMembers($author, $commenter);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['visibility' => 'members', 'status' => 'published']);

    $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => $commenter->id,
        'body' => 'Commenter\'s comment',
    ]);

    actingAs($author)
        ->deleteJson(
            route('communities.posts.comments.destroy', [
                'community' => $community,
                'post' => $post,
                'comment' => $comment,
            ])
        )
        ->assertSuccessful();

    expect(Comment::count())->toBe(0);
});

test('comment requires body', function () {
    $author = createVerifiedUser();
    $commenter = createVerifiedUser();
    $community = createCommunityWithMembers($author, $commenter);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['visibility' => 'members', 'status' => 'published']);

    actingAs($commenter)
        ->postJson(
            route('communities.posts.comments.store', [
                'community' => $community,
                'post' => $post,
            ]),
            [
                'body' => '',
            ]
        )
        ->assertStatus(422);
});

test('comments include nested replies', function () {
    $author = createVerifiedUser();
    $commenter = createVerifiedUser();
    $community = createCommunityWithMembers($author, $commenter);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['visibility' => 'members', 'status' => 'published']);

    // Create top-level comment
    $parent = Comment::create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'body' => 'Parent comment',
    ]);

    // Create reply
    Comment::create([
        'post_id' => $post->id,
        'user_id' => $commenter->id,
        'body' => 'Reply to parent',
        'parent_comment_id' => $parent->id,
    ]);

    actingAs($commenter)
        ->getJson(
            route('communities.posts.comments.index', [
                'community' => $community,
                'post' => $post,
            ])
        )
        ->assertSuccessful()
        ->assertJsonCount(1, 'comments')
        ->assertJsonCount(1, 'comments.0.replies');
});
