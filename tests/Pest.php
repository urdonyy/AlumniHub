<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

// expect()->extend('toBeOne', function () {
//     return $this->toBe(1);
// });

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Str;

function createVerifiedUser()
{
    return User::factory()->create([
        'role' => 'alumni',
        'account_status' => 'approved',
    ]);
}

function createCommunityWithMembers(...$users): Community
{
    $community = Community::create([
        'name' => 'Test Community',
        'slug' => Str::slug('Test Community') . '-' . Str::random(6),
        'description' => 'Test community description',
        'created_by' => $users[0]->id,
    ]);

    $userIds = collect($users)->map(fn($u) => $u->id)->toArray();
    $community->members()->attach($userIds);

    return $community;
}
