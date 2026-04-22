<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

function tinyPngContent(): string
{
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAoMBgQmR4TgAAAAASUVORK5CYII=');
}

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile show page requires authentication', function () {
    $user = User::factory()->create();

    $response = get('/profiles/' . $user->id);

    $response->assertRedirect('/login');
});

test('profile update requires authentication', function () {
    $currentYear = (int) now()->format('Y');

    $response = patch('/profile', [
        'first_name' => 'Guest',
        'last_name' => 'User',
        'batch_year' => $currentYear,
        'program_course' => 'Diploma in Information Communication Technology (DICT)',
        'email' => 'guest@example.com',
    ]);

    $response->assertRedirect('/login');
});

test('profile deletion requires authentication', function () {
    $response = delete('/profile', [
        'password' => 'password',
    ]);

    $response->assertRedirect('/login');
});

test('authenticated users can view other profiles', function () {
    $viewer = User::factory()->create();
    $profileUser = User::factory()->create([
        'name' => 'Profile Owner',
        'first_name' => 'Profile',
        'last_name' => 'Owner',
    ]);

    $response = actingAs($viewer)->get('/profiles/' . $profileUser->id);

    $response->assertOk()->assertSee('Profile Owner');
});

test('profile show uses default media fallbacks when media paths are empty', function () {
    $viewer = User::factory()->create();
    $profileUser = User::factory()->create([
        'avatar_path' => null,
        'banner_path' => null,
    ]);

    $response = actingAs($viewer)->get('/profiles/' . $profileUser->id);

    $response
        ->assertOk()
        ->assertSee('images/default-banner.svg')
        ->assertSee('images/default-avatar.svg');
});

test('profile show uses stored media urls when media paths exist', function () {
    $viewer = User::factory()->create();
    $profileUser = User::factory()->create([
        'avatar_path' => 'avatars/user_99/avatar.png',
        'banner_path' => 'banners/user_99/banner.png',
    ]);

    $response = actingAs($viewer)->get('/profiles/' . $profileUser->id);

    $response
        ->assertOk()
        ->assertSee('/storage/avatars/user_99/avatar.png')
        ->assertSee('/storage/banners/user_99/banner.png');
});

test('profile information can be updated', function () {
    $currentYear = (int) now()->format('Y');
    $programCourse = 'Diploma in Information Communication Technology (DICT)';

    $user = User::factory()->create();

    $response = actingAs($user)
        ->patch('/profile', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'batch_year' => $currentYear,
            'program_course' => $programCourse,
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('Test', $user->first_name);
    $this->assertSame('User', $user->last_name);
    $this->assertSame($currentYear, $user->batch_year);
    $this->assertSame($programCourse, $user->program_course);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $currentYear = (int) now()->format('Y');
    $programCourse = 'Diploma in Information Communication Technology (DICT)';

    $user = User::factory()->create();

    $response = actingAs($user)
        ->patch('/profile', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'batch_year' => $currentYear,
            'program_course' => $programCourse,
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('role and account status cannot be updated through profile update', function () {
    $currentYear = (int) now()->format('Y');
    $programCourse = 'Diploma in Information Communication Technology (DICT)';

    $user = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'pending',
    ]);

    $response = actingAs($user)
        ->patch('/profile', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'batch_year' => $currentYear,
            'program_course' => $programCourse,
            'email' => 'test2@example.com',
            'role' => 'admin',
            'account_status' => 'approved',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('alumni', $user->role);
    $this->assertSame('pending', $user->account_status);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    assertGuest();
    $this->assertNull($user->fresh());
});

test('deleting account removes uploaded profile media files', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    actingAs($user)
        ->patch('/profile', [
            'first_name' => 'Media',
            'last_name' => 'Owner',
            'batch_year' => (int) now()->format('Y'),
            'program_course' => 'Diploma in Information Communication Technology (DICT)',
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->createWithContent('avatar-delete.png', tinyPngContent()),
            'banner' => UploadedFile::fake()->createWithContent('banner-delete.png', tinyPngContent()),
        ])
        ->assertSessionHasNoErrors();

    $user->refresh();

    $avatarPath = $user->avatar_path;
    $bannerPath = $user->banner_path;

    expect(Storage::disk('public')->exists($avatarPath))->toBeTrue();
    expect(Storage::disk('public')->exists($bannerPath))->toBeTrue();

    actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    expect(Storage::disk('public')->exists($avatarPath))->toBeFalse();
    expect(Storage::disk('public')->exists($bannerPath))->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test('profile media can be uploaded', function () {
    Storage::fake('public');

    $currentYear = (int) now()->format('Y');
    $programCourse = 'Diploma in Information Communication Technology (DICT)';
    $user = User::factory()->create();

    $response = actingAs($user)
        ->patch('/profile', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'batch_year' => $currentYear,
            'program_course' => $programCourse,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->createWithContent('avatar.png', tinyPngContent()),
            'banner' => UploadedFile::fake()->createWithContent('banner.png', tinyPngContent()),
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();
    expect($user->banner_path)->not->toBeNull();
    expect($user->avatar_uploaded_at)->not->toBeNull();
    expect($user->banner_uploaded_at)->not->toBeNull();

    expect(Storage::disk('public')->exists($user->avatar_path))->toBeTrue();
    expect(Storage::disk('public')->exists($user->banner_path))->toBeTrue();
});

test('replacing profile media removes old files', function () {
    Storage::fake('public');

    $currentYear = (int) now()->format('Y');
    $programCourse = 'Diploma in Information Communication Technology (DICT)';
    $user = User::factory()->create();

    actingAs($user)
        ->patch('/profile', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'batch_year' => $currentYear,
            'program_course' => $programCourse,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->createWithContent('avatar-1.png', tinyPngContent()),
            'banner' => UploadedFile::fake()->createWithContent('banner-1.png', tinyPngContent()),
        ])
        ->assertSessionHasNoErrors();

    $user->refresh();
    $oldAvatarPath = $user->avatar_path;
    $oldBannerPath = $user->banner_path;

    actingAs($user)
        ->patch('/profile', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'batch_year' => $currentYear,
            'program_course' => $programCourse,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->createWithContent('avatar-2.png', tinyPngContent()),
            'banner' => UploadedFile::fake()->createWithContent('banner-2.png', tinyPngContent()),
        ])
        ->assertSessionHasNoErrors();

    $user->refresh();

    expect(Storage::disk('public')->exists($oldAvatarPath))->toBeFalse();
    expect(Storage::disk('public')->exists($oldBannerPath))->toBeFalse();
    expect(Storage::disk('public')->exists($user->avatar_path))->toBeTrue();
    expect(Storage::disk('public')->exists($user->banner_path))->toBeTrue();
});

test('invalid profile media file types are rejected', function () {
    Storage::fake('public');

    $currentYear = (int) now()->format('Y');
    $programCourse = 'Diploma in Information Communication Technology (DICT)';
    $user = User::factory()->create();

    $response = actingAs($user)
        ->from('/profile')
        ->patch('/profile', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'batch_year' => $currentYear,
            'program_course' => $programCourse,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->create('avatar.pdf', 200, 'application/pdf'),
        ]);

    $response
        ->assertSessionHasErrors(['avatar'])
        ->assertRedirect('/profile');
});
