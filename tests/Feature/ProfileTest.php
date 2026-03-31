<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->get('/profile');

    $response->assertOk();
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
