<?php

use App\Models\User;
use App\Models\VerificationDocument;
use App\Notifications\VerificationStatusChanged;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    Storage::fake('local');
    Notification::fake();
});

test('authenticated alumni can upload a verification document', function () {
    $user = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'pending',
    ]);

    $response = actingAs($user)->post(route('verification.store'), [
        'document' => UploadedFile::fake()->create('alumni-id.pdf', 120, 'application/pdf'),
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $document = VerificationDocument::query()->latest()->first();

    expect($document)->not->toBeNull();
    expect($document->user_id)->toBe($user->id);
    expect($document->status)->toBe('pending');
    expect($document->document_path)->not->toBeEmpty();

    Storage::assertExists($document->document_path);
    assertDatabaseHas('verification_documents', [
        'user_id' => $user->id,
        'status' => 'pending',
    ]);
});

test('non admin users cannot access the verification queue', function () {
    $user = User::factory()->create([
        'role' => 'alumni',
    ]);

    actingAs($user)
        ->get(route('admin.verifications.index'))
        ->assertForbidden();
});

test('admin can view an uploaded verification document', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'approved',
    ]);

    $user = User::factory()->create([
        'role' => 'alumni',
    ]);

    Storage::disk('local')->put('verifications/user_' . $user->id . '/sample.pdf', 'fake pdf content');

    $document = VerificationDocument::create([
        'user_id' => $user->id,
        'document_path' => 'verifications/user_' . $user->id . '/sample.pdf',
        'status' => 'pending',
    ]);

    actingAs($admin)
        ->get(route('admin.verifications.document', $document))
        ->assertOk();
});

test('admin can approve a verification document and notify the user', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'approved',
    ]);

    $user = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'pending',
    ]);

    Storage::disk('local')->put('verifications/user_' . $user->id . '/sample.pdf', 'fake pdf content');

    $document = VerificationDocument::create([
        'user_id' => $user->id,
        'document_path' => 'verifications/user_' . $user->id . '/sample.pdf',
        'status' => 'pending',
    ]);

    $response = actingAs($admin)
        ->patch(route('admin.verifications.approve', $document), [
            'admin_notes' => 'Approved after manual review.',
        ]);

    $response->assertSessionHas('status', 'verification-approved');

    $document->refresh();
    $user->refresh();

    expect($document->status)->toBe('approved');
    expect($document->reviewed_by)->toBe($admin->id);
    expect($document->reviewed_at)->not->toBeNull();
    expect($document->admin_notes)->toBe('Approved after manual review.');
    expect($user->account_status)->toBe('approved');

    Notification::assertSentTo($user, VerificationStatusChanged::class, function (VerificationStatusChanged $notification) use ($document) {
        return $notification->verificationDocument->is($document);
    });
});

test('admin can reject a verification document and notify the user', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'approved',
    ]);

    $user = User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'pending',
    ]);

    Storage::disk('local')->put('verifications/user_' . $user->id . '/sample.pdf', 'fake pdf content');

    $document = VerificationDocument::create([
        'user_id' => $user->id,
        'document_path' => 'verifications/user_' . $user->id . '/sample.pdf',
        'status' => 'pending',
    ]);

    $response = actingAs($admin)
        ->patch(route('admin.verifications.reject', $document), [
            'admin_notes' => 'The document is unclear.',
        ]);

    $response->assertSessionHas('status', 'verification-rejected');

    $document->refresh();
    $user->refresh();

    expect($document->status)->toBe('rejected');
    expect($document->reviewed_by)->toBe($admin->id);
    expect($document->reviewed_at)->not->toBeNull();
    expect($document->admin_notes)->toBe('The document is unclear.');
    expect($user->account_status)->toBe('rejected');

    Notification::assertSentTo($user, VerificationStatusChanged::class, function (VerificationStatusChanged $notification) use ($document) {
        return $notification->verificationDocument->is($document);
    });
});
