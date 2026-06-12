<?php

use App\Models\Post;
use App\Models\PostReport;
use App\Models\User;
use App\Notifications\PostRemovedByReportNotification;
use Illuminate\Support\Facades\Notification;
use function Pest\Laravel\actingAs;

function reportablePost(User $author, $community, string $visibility = 'members'): Post
{
    return Post::factory()->for($author)->for($community)->create([
        'visibility' => $visibility,
        'status' => 'published',
    ]);
}

test('a verified member can report a post they can see', function () {
    $author = createVerifiedUser();
    $reporter = createVerifiedUser();
    $community = createCommunityWithMembers($author, $reporter);
    $post = reportablePost($author, $community);

    actingAs($reporter)
        ->postJson(route('posts.report', $post), ['reason' => 'racism', 'details' => 'hateful'])
        ->assertSuccessful()
        ->assertJson(['success' => true]);

    expect(PostReport::count())->toBe(1);
    $report = PostReport::first();
    expect($report->user_id)->toBe($reporter->id);
    expect($report->reason)->toBe('racism');
    expect($post->fresh()->reports_count)->toBe(1);
    expect($post->fresh()->flagged_at)->toBeNull();
});

test('an author cannot report their own post', function () {
    $author = createVerifiedUser();
    $community = createCommunityWithMembers($author);
    $post = reportablePost($author, $community);

    actingAs($author)
        ->postJson(route('posts.report', $post), ['reason' => 'spam'])
        ->assertStatus(422);

    expect(PostReport::count())->toBe(0);
});

test('an unverified user cannot report a post', function () {
    $author = createVerifiedUser();
    $unverified = User::factory()->create(['role' => 'alumni', 'account_status' => 'pending']);
    $community = createCommunityWithMembers($author, $unverified);
    $post = reportablePost($author, $community);

    actingAs($unverified)
        ->postJson(route('posts.report', $post), ['reason' => 'spam'])
        ->assertStatus(403);

    expect(PostReport::count())->toBe(0);
});

test('a user cannot report a post they cannot see', function () {
    $author = createVerifiedUser();
    $outsider = createVerifiedUser();
    // Connections-only post, and the outsider is not connected.
    $post = Post::factory()->for($author)->create([
        'community_id' => null,
        'visibility' => 'connections',
        'status' => 'published',
    ]);

    actingAs($outsider)
        ->postJson(route('posts.report', $post), ['reason' => 'spam'])
        ->assertStatus(403);

    expect(PostReport::count())->toBe(0);
});

test('reporting the same post twice does not create a second report', function () {
    $author = createVerifiedUser();
    $reporter = createVerifiedUser();
    $community = createCommunityWithMembers($author, $reporter);
    $post = reportablePost($author, $community);

    actingAs($reporter)->postJson(route('posts.report', $post), ['reason' => 'spam'])->assertSuccessful();
    actingAs($reporter)->postJson(route('posts.report', $post), ['reason' => 'fraud'])
        ->assertSuccessful()
        ->assertJson(['already_reported' => true]);

    expect(PostReport::count())->toBe(1);
    expect($post->fresh()->reports_count)->toBe(1);
});

test('a post is flagged for review once it hits the report threshold', function () {
    $author = createVerifiedUser();
    $reporters = collect(range(1, PostReport::THRESHOLD))->map(fn () => createVerifiedUser());
    $community = createCommunityWithMembers($author, ...$reporters->all());
    $post = reportablePost($author, $community);

    foreach ($reporters as $reporter) {
        actingAs($reporter)->postJson(route('posts.report', $post), ['reason' => 'nsfw'])->assertSuccessful();
    }

    $post->refresh();
    expect($post->reports_count)->toBe(PostReport::THRESHOLD);
    expect($post->flagged_at)->not->toBeNull();
});

test('admin keep dismisses pending reports and clears the flag', function () {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'approved']);
    $author = createVerifiedUser();
    $community = createCommunityWithMembers($author);
    $post = reportablePost($author, $community);
    $post->forceFill(['reports_count' => PostReport::THRESHOLD, 'flagged_at' => now()])->save();
    $post->reports()->create(['user_id' => $author->id, 'reason' => 'spam']);

    actingAs($admin)->post(route('admin.reports.keep', $post))->assertRedirect();

    $post->refresh();
    expect($post->reports_count)->toBe(0);
    expect($post->flagged_at)->toBeNull();
    expect($post->trashed_at)->toBeNull();
    expect($post->reports()->pending()->count())->toBe(0);
});

test('admin remove deletes the post and notifies the author with a reason', function () {
    Notification::fake();

    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'approved']);
    $author = createVerifiedUser();
    $community = createCommunityWithMembers($author);
    $post = reportablePost($author, $community);
    $post->forceFill(['reports_count' => PostReport::THRESHOLD, 'flagged_at' => now()])->save();

    actingAs($admin)
        ->delete(route('admin.reports.destroy', $post), ['reason' => 'racism', 'note' => 'final warning'])
        ->assertRedirect();

    expect(Post::find($post->id))->toBeNull();

    Notification::assertSentTo($author, PostRemovedByReportNotification::class, function ($n) use ($author) {
        $data = $n->toArray($author);
        return $data['type'] === 'post_removed_violation'
            && $data['reason'] === 'racism'
            && str_contains($data['message'], 'suspended');
    });
});
