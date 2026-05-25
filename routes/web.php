<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\Admin\CommunityAdminController;
use App\Http\Controllers\Admin\FlairAdminController;
use App\Http\Controllers\Admin\VerificationAdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerificationController;
use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Services\FeedService;

Route::get('/', function (FeedService $feed) {
    $user = auth()->user();

    if ($user) {
        /** @var \App\Models\User $user */
        $posts = $feed->getUserFeed($user, 10);

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

        $joinedCommunities = $user->communities()
            ->orderBy('name')
            ->get(['communities.id', 'communities.name', 'communities.system_key']);

        // Available flairs: global and from joined communities
        $availableFlairs = \App\Models\Flair::query()
            ->whereNull('community_id')
            ->orWhereIn('community_id', $joinedCommunities->pluck('id'))
            ->get();

        return view('dashboard', [
            'posts' => $posts,
            'featuredCommunities' => $featuredCommunities,
            'suggestedPeople' => $suggestedPeople,
            'joinedCommunities' => $joinedCommunities,
            'availableFlairs' => $availableFlairs,
        ]);
    }

    return view('welcome');
});

Route::get('/dashboard', function (Request $request, FeedService $feed) {
    $user = $request->user();
    /** @var \App\Models\User $user */

    $posts = $feed->getUserFeed($user, 10);

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

    $joinedCommunities = $user->communities()
        ->orderBy('name')
        ->get(['communities.id', 'communities.name', 'communities.system_key']);

    $availableFlairs = \App\Models\Flair::query()
        ->whereNull('community_id')
        ->orWhereIn('community_id', $joinedCommunities->pluck('id'))
        ->get();

    return view('dashboard', [
        'posts' => $posts,
        'featuredCommunities' => $featuredCommunities,
        'suggestedPeople' => $suggestedPeople,
        'joinedCommunities' => $joinedCommunities,
        'availableFlairs' => $availableFlairs,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
    Route::get('/communities/{community}', [CommunityController::class, 'show'])->name('communities.show');
    Route::post('/communities/{community}/join', [MembershipController::class, 'join'])->name('communities.join');
    Route::delete('/communities/{community}/leave', [MembershipController::class, 'leave'])->name('communities.leave');

    // Posts - read routes
    Route::resource('communities.posts', CommunityPostController::class)
        ->only(['index', 'show']);

    // Posts - write routes (member-only)
    Route::middleware('ensure.community.member')
        ->resource('communities.posts', CommunityPostController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy']);

    // Dashboard quick post (modal composer with community picker)
    Route::post('/posts/quick-store', [CommunityPostController::class, 'quickStore'])
        ->name('posts.quick-store');

    // Comments - CRUD routes
    Route::middleware('auth')->group(function () {
        Route::get('/communities/{community}/posts/{post}/api', [CommentController::class, 'getPostDetails'])
            ->name('communities.posts.api');
        Route::get('/communities/{community}/posts/{post}/comments', [CommentController::class, 'index'])
            ->name('communities.posts.comments.index');
        Route::post('/communities/{community}/posts/{post}/comments', [CommentController::class, 'store'])
            ->name('communities.posts.comments.store');
        Route::delete('/communities/{community}/posts/{post}/comments/{comment}', [CommentController::class, 'destroy'])
            ->name('communities.posts.comments.destroy');
    });

    // Likes - toggle route
    Route::post('/communities/{community}/posts/{post}/like', [PostLikeController::class, 'toggle'])
        ->middleware('auth')
        ->name('communities.posts.like');
    // Route::get('/communities/{community}/posts/{post}', [PostController::class, 'show'])->name('communities.posts.show');

    // // Posts - create/edit/delete (requires membership)
    // Route::middleware('ensure.community.member')->resource('communities.posts', CommunityPostController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

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

    Route::get('/connections', [ConnectionController::class, 'index'])->name('connections.index');
    Route::post('/connections/invite/{user}', [ConnectionController::class, 'invite'])->name('connections.invite');
    Route::post('/connections/{connection}/accept', [ConnectionController::class, 'accept'])->name('connections.accept');
    Route::post('/connections/{connection}/ignore', [ConnectionController::class, 'ignore'])->name('connections.ignore');

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

    Route::resource('flairs', FlairAdminController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::get('/verifications', [VerificationAdminController::class, 'index'])->name('verifications.index');
    Route::get('/verifications/{verificationDocument}/document', [VerificationAdminController::class, 'viewDocument'])->name('verifications.document');
    Route::patch('/verifications/{verificationDocument}/approve', [VerificationAdminController::class, 'approve'])->name('verifications.approve');
    Route::patch('/verifications/{verificationDocument}/reject', [VerificationAdminController::class, 'reject'])->name('verifications.reject');
});

require __DIR__ . '/auth.php';
