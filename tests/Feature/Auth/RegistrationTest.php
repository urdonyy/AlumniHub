<?php

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

test('new users can register', function () {
    $currentYear = (int) now()->format('Y');
    $programCourse = 'Diploma in Information Communication Technology (DICT)';

    $response = post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'role' => 'alumni',
        'batch_year' => $currentYear,
        'program_course' => $programCourse,
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    assertDatabaseHas('users', [
        'name' => 'Test User',
        'first_name' => 'Test',
        'last_name' => 'User',
        'role' => 'alumni',
        'account_status' => 'pending',
        'batch_year' => $currentYear,
        'program_course' => $programCourse,
        'email' => 'test@example.com',
    ]);
});
