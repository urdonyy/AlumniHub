<?php

use App\Models\Community;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

test('user can like a post', function () {
    $user = createVerifiedUser();
    $author = createVerifiedUser();
    $community = createCommunityWithMembers($user, $author);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['like_count' => 0]);

    $response = actingAs($user)
        ->post(route('communities.posts.like', ['community' => $community, 'post' => $post]));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'liked' => true,
            'like_count' => 1,
        ]);

    expect($post->likes()->where('user_id', $user->id)->exists())->toBeTrue();
});

test('user can unlike a post', function () {
    $user = createVerifiedUser();
    $author = createVerifiedUser();
    $community = createCommunityWithMembers($user, $author);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['like_count' => 1]);

    // First like the post
    Like::create([
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);

    // Then unlike
    $response = actingAs($user)
        ->post(route('communities.posts.like', ['community' => $community, 'post' => $post]));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'liked' => false,
            'like_count' => 0,
        ]);

    expect($post->likes()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('unauthenticated user cannot like post', function () {
    $community = Community::create([
        'name' => 'Test Community',
        'slug' => Str::slug('Test Community') . '-' . Str::random(6),
        'description' => 'Test community description',
        'created_by' => createVerifiedUser()->id,
    ]);
    $post = Post::factory()->for($community)->create();

    $response = post(route('communities.posts.like', ['community' => $community, 'post' => $post]));

    $response->assertRedirect('/login');
});

test('user cannot like unauthorized post', function () {
    $user = createVerifiedUser();
    $author = createVerifiedUser();
    $community = Community::create([
        'name' => 'Test Community',
        'slug' => Str::slug('Test Community') . '-' . Str::random(6),
        'description' => 'Test community description',
        'created_by' => $author->id,
    ]);
    $community->members()->attach($author->id);
    // User is NOT a member of this community

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['visibility' => 'members']);

    $response = actingAs($user)
        ->post(route('communities.posts.like', ['community' => $community, 'post' => $post]));

    $response->assertForbidden();
});

test('like count increments correctly', function () {
    $community = Community::create([
        'name' => 'Test Community',
        'slug' => Str::slug('Test Community') . '-' . Str::random(6),
        'description' => 'Test community description',
        'created_by' => createVerifiedUser()->id,
    ]);

    $user1 = createVerifiedUser();
    $user2 = createVerifiedUser();
    $author = createVerifiedUser();

    $community->members()->attach([$user1->id, $user2->id, $author->id]);

    $post = Post::factory()
        ->for($author)
        ->for($community)
        ->create(['like_count' => 0]);

    // User 1 likes
    $response1 = actingAs($user1)
        ->post(route('communities.posts.like', ['community' => $community, 'post' => $post]));

    $response1->assertJson(['like_count' => 1]);

    // User 2 likes
    $response2 = actingAs($user2)
        ->post(route('communities.posts.like', ['community' => $community, 'post' => $post]));

    $response2->assertJson(['like_count' => 2]);

    // Refresh and verify
    $post->refresh();
    expect($post->like_count)->toBe(2);
});
