<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\Admin\CommunityAdminController;
use App\Http\Controllers\Admin\FlairAdminController;
use App\Http\Controllers\Admin\VerificationAdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerificationController;
use App\Models\Community;
use App\Models\Connection;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\FeedService;

Route::get('/', function (FeedService $feed) {
    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    if ($user) {
        if (empty($user->first_name)) {
            return redirect()->route('register.complete');
        }

        $posts = $feed->getUserFeed($user, 10);

        $featuredCommunities = Community::query()
            ->withCount('members')
            ->orderByDesc('members_count')
            ->limit(5)
            ->get();

        $excludedIds = Connection::query()
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
            })
            ->whereIn('status', [Connection::STATUS_PENDING, Connection::STATUS_ACCEPTED])
            ->get(['sender_id', 'recipient_id'])
            ->flatMap(fn ($c) => [$c->sender_id, $c->recipient_id])
            ->push($user->id)
            ->unique()
            ->values()
            ->all();

        $suggestedPeople = User::query()
            ->whereIn('role', ['alumni', 'student'])
            ->where('account_status', 'approved')
            ->whereNotIn('id', $excludedIds)
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

        $flairsByCommunity = $availableFlairs
            ->groupBy(fn($f) => $f->community_id ?? 'global')
            ->map(fn($group) => $group->values()->map(fn($f) => [
                'id' => $f->id, 'name' => $f->name, 'color' => $f->color, 'icon' => $f->icon,
            ]));

        return view('dashboard', [
            'posts' => $posts,
            'featuredCommunities' => $featuredCommunities,
            'suggestedPeople' => $suggestedPeople,
            'joinedCommunities' => $joinedCommunities,
            'availableFlairs' => $availableFlairs,
            'flairsByCommunity' => $flairsByCommunity,
        ]);
    }

    return view('welcome');
});

Route::get('/dashboard', function (Request $request, FeedService $feed) {
    $user = $request->user();
    /** @var \App\Models\User $user */

    if (empty($user->first_name)) {
        return redirect()->route('register.complete');
    }

    $posts = $feed->getUserFeed($user, 10);

    $featuredCommunities = Community::query()
        ->withCount('members')
        ->orderByDesc('members_count')
        ->limit(5)
        ->get();

    $excludedIds = Connection::query()
        ->where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
        })
        ->whereIn('status', [Connection::STATUS_PENDING, Connection::STATUS_ACCEPTED])
        ->get(['sender_id', 'recipient_id'])
        ->flatMap(fn ($c) => [$c->sender_id, $c->recipient_id])
        ->push($user->id)
        ->unique()
        ->values()
        ->all();

    $suggestedPeople = User::query()
        ->whereIn('role', ['alumni', 'student'])
        ->where('account_status', 'approved')
        ->whereNotIn('id', $excludedIds)
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

    $flairsByCommunity = $availableFlairs
        ->groupBy(fn($f) => $f->community_id ?? 'global')
        ->map(fn($group) => $group->values()->map(fn($f) => [
            'id' => $f->id, 'name' => $f->name, 'color' => $f->color, 'icon' => $f->icon,
        ]));

    return view('dashboard', [
        'posts' => $posts,
        'featuredCommunities' => $featuredCommunities,
        'suggestedPeople' => $suggestedPeople,
        'joinedCommunities' => $joinedCommunities,
        'availableFlairs' => $availableFlairs,
        'flairsByCommunity' => $flairsByCommunity,
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
        Route::get('/communities/{community}/posts/{post}/open', function (Community $community, Post $post, Request $request) {
            if ((int) $post->community_id !== (int) $community->id) {
                abort(404);
            }

            $request->user()?->can('view', $post) ?: abort(403);

            return redirect()->route('dashboard')->with('openPostModal', [
                'postId' => $post->id,
                'apiUrl' => route('communities.posts.api', ['community' => $community->id, 'post' => $post->id]),
                'commentsUrl' => route('communities.posts.comments.index', ['community' => $community->id, 'post' => $post->id]),
            ]);
        })->name('communities.posts.open');
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

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/connections', [ConnectionController::class, 'index'])->name('connections.index');
    Route::get('/connections/counts', [ConnectionController::class, 'counts'])->name('connections.counts');
    Route::post('/connections/invite/{user}', [ConnectionController::class, 'invite'])->name('connections.invite');
    Route::post('/connections/{connection}/accept', [ConnectionController::class, 'accept'])->name('connections.accept');
    Route::post('/connections/{connection}/ignore', [ConnectionController::class, 'ignore'])->name('connections.ignore');
    Route::delete('/connections/{connection}/withdraw', [ConnectionController::class, 'withdraw'])->name('connections.withdraw');
    Route::delete('/connections/{connection}/remove', [ConnectionController::class, 'remove'])->name('connections.remove');

    
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
