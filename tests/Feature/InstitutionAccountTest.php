<?php

use App\Http\Controllers\InstitutionSwitchController;
use App\Models\CommunityCreationRequest;
use App\Models\Post;
use App\Models\User;
use App\Services\CommunityCreationRequestService;
use function Pest\Laravel\actingAs;

function institutionAccount(): User
{
    return User::factory()->create([
        'role' => 'superadmin',
        'account_status' => 'approved',
        'name' => 'PUP-ITECH Official',
    ]);
}

function adminAccount(): User
{
    return User::factory()->create(['role' => 'admin', 'account_status' => 'approved']);
}

test('the institution account is verified and resolvable', function () {
    $institution = institutionAccount();

    expect($institution->isInstitution())->toBeTrue();
    expect($institution->isVerified())->toBeTrue();
    expect($institution->canManageCommunities())->toBeFalse(); // not the admin panel
    expect(User::institution()?->id)->toBe($institution->id);
});

test('an admin can switch into the institution account', function () {
    $admin = adminAccount();
    $institution = institutionAccount();

    actingAs($admin)
        ->post(route('admin.institution.enter'))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas(InstitutionSwitchController::SESSION_KEY, $admin->id);

    expect(auth()->id())->toBe($institution->id);
});

test('a non-admin cannot switch into the institution account', function () {
    institutionAccount();
    $client = createVerifiedUser();

    actingAs($client)->post(route('admin.institution.enter'))->assertForbidden();
});

test('exiting the switch returns to the original admin', function () {
    $admin = adminAccount();
    $institution = institutionAccount();

    actingAs($institution)
        ->withSession([InstitutionSwitchController::SESSION_KEY => $admin->id])
        ->post(route('institution.exit'))
        ->assertRedirect(route('admin.inbox'))
        ->assertSessionMissing(InstitutionSwitchController::SESSION_KEY);

    expect(auth()->id())->toBe($admin->id);
});

test('the institution can remove a system community post it did not author', function () {
    $institution = institutionAccount();
    $author = createVerifiedUser();
    $community = createCommunityWithMembers($author, $institution);
    $community->update(['is_system' => true]);
    $post = Post::factory()->for($author)->for($community)->create([
        'visibility' => 'members',
        'status' => 'published',
    ]);

    actingAs($institution)->delete(route('posts.trash', $post));

    expect($post->fresh()->trashed_at)->not->toBeNull();
});

test('the institution cannot remove a batch community post', function () {
    $institution = institutionAccount();
    $author = createVerifiedUser();
    // A batch community is non-system; the institution is a plain non-member there.
    $community = createCommunityWithMembers($author);
    $post = Post::factory()->for($author)->for($community)->create([
        'visibility' => 'public',
        'status' => 'published',
    ]);

    actingAs($institution)->delete(route('posts.trash', $post))->assertForbidden();

    expect($post->fresh()->trashed_at)->toBeNull();
});

test('the institution cannot remove a community-less connections post', function () {
    $institution = institutionAccount();
    $author = createVerifiedUser();
    $post = Post::factory()->for($author)->create([
        'community_id' => null,
        'visibility' => 'connections',
        'status' => 'published',
    ]);

    actingAs($institution)->delete(route('posts.trash', $post))->assertForbidden();

    expect($post->fresh()->trashed_at)->toBeNull();
});

test('the institution cannot request a batch community', function () {
    $institution = institutionAccount();

    actingAs($institution)->get(route('communities.requests.create'))->assertForbidden();
});

test('the institution cannot open the profile settings form', function () {
    $institution = institutionAccount();

    actingAs($institution)->get(route('profile.edit'))->assertForbidden();
});

test('the institution can update its avatar/banner but not its profile info', function () {
    Illuminate\Support\Facades\Storage::fake();
    $institution = institutionAccount();

    actingAs($institution)->patch(route('profile.update'), [
        'first_name' => 'Hacked',
        'last_name' => 'Name',
        'email' => $institution->email,
        'avatar' => Illuminate\Http\UploadedFile::fake()->image('logo.png', 200, 200),
    ])->assertRedirect();

    $institution->refresh();
    expect($institution->avatar_path)->not->toBeNull();      // branding updated
    expect($institution->name)->toBe('PUP-ITECH Official');  // info ignored
    expect($institution->first_name)->not->toBe('Hacked');
});

test('the institution does NOT auto-join a newly approved batch community', function () {
    $institution = institutionAccount();
    $admin = adminAccount();
    $requestor = createVerifiedUser();

    $request = CommunityCreationRequest::create([
        'requestor_id' => $requestor->id,
        'name' => 'DICT Batch 2024',
        'description' => 'Test batch community',
        'purpose' => 'Networking',
        'batch_year' => 2024,
        'program_course' => 'Diploma in Information Communication Technology (DICT)',
        'year_section' => '3-1',
        'status' => CommunityCreationRequest::STATUS_PENDING_ADMIN,
    ]);

    $community = app(CommunityCreationRequestService::class)->approve($request, $admin);

    // Batch communities belong to their own batch members, not the institution.
    expect($community->members()->whereKey($institution->id)->exists())->toBeFalse();
});

test('the institution is suggested to verified members on the connections page', function () {
    $institution = institutionAccount();
    $client = createVerifiedUser();

    actingAs($client)
        ->get(route('connections.index'))
        ->assertOk()
        ->assertSee('PUP-ITECH Official');
});
