<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\Admin\CommunityAdminController;
use App\Http\Controllers\Admin\VerificationAdminController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerificationController;
use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = request()->user();

    $featuredCommunities = Community::query()
        ->withCount('members')
        ->orderByDesc('members_count')
        ->limit(5)
        ->get();

    $suggestedPeople = User::query()
        ->where('id', '!=', $user->id)
        ->where('role', 'alumni')
        ->orderBy('name')
        ->limit(5)
        ->get(['id', 'name', 'batch_year', 'program_course', 'account_status']);

    return view('dashboard', [
        'featuredCommunities' => $featuredCommunities,
        'suggestedPeople' => $suggestedPeople,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
    Route::get('/communities/{community}', [CommunityController::class, 'show'])->name('communities.show');
    Route::post('/communities/{community}/join', [MembershipController::class, 'join'])->name('communities.join');
    Route::delete('/communities/{community}/leave', [MembershipController::class, 'leave'])->name('communities.leave');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profiles/{user}', [ProfileController::class, 'show'])->name('profiles.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/verification', [VerificationController::class, 'store'])->name('verification.store');

    Route::view('/messages', 'placeholders.module', [
        'title' => 'Messages',
        'description' => 'Direct messaging will be available after the messaging backend is implemented.',
    ])->name('messages.index');

    Route::view('/notifications', 'placeholders.module', [
        'title' => 'Notifications',
        'description' => 'Notification center will be enabled once activity feed and social actions are integrated.',
    ])->name('notifications.index');

    Route::view('/connections', 'placeholders.module', [
        'title' => 'Connections',
        'description' => 'Connections and follow requests are planned for a future phase.',
    ])->name('connections.index');

    Route::view('/saved', 'placeholders.module', [
        'title' => 'Saved',
        'description' => 'Saved posts and resources will be available after post features are ready.',
    ])->name('saved.index');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/communities', [CommunityAdminController::class, 'index'])->name('communities.index');
    Route::post('/communities', [CommunityAdminController::class, 'store'])->name('communities.store');
    Route::patch('/communities/{community}', [CommunityAdminController::class, 'update'])->name('communities.update');
    Route::delete('/communities/{community}', [CommunityAdminController::class, 'destroy'])->name('communities.destroy');

    Route::get('/verifications', [VerificationAdminController::class, 'index'])->name('verifications.index');
    Route::get('/verifications/{verificationDocument}/document', [VerificationAdminController::class, 'viewDocument'])->name('verifications.document');
    Route::patch('/verifications/{verificationDocument}/approve', [VerificationAdminController::class, 'approve'])->name('verifications.approve');
    Route::patch('/verifications/{verificationDocument}/reject', [VerificationAdminController::class, 'reject'])->name('verifications.reject');
});

require __DIR__ . '/auth.php';
