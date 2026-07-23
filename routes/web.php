<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\Admin\CommunityAdminController;
use App\Http\Controllers\Admin\CommunityCreationRequestAdminController;
use App\Http\Controllers\Admin\FlairAdminController;
use App\Http\Controllers\Admin\AdminInboxController;
use App\Http\Controllers\Admin\PostReportAdminController;
use App\Http\Controllers\Admin\VerificationAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityCreationRequestController;
use App\Http\Controllers\CommunityJoinRequestController;
use App\Http\Controllers\CommunityModerationController;
use App\Http\Controllers\CommunityModeratorTransferController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\CoModeratorInviteController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\InstitutionSwitchController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\PostModerationController;
use App\Http\Controllers\PostReportController;
use App\Http\Controllers\PostTrashController;
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

        $selectedFlairIds = array_values(array_unique(array_map('intval', array_filter((array) request()->query('flairs', [])))));
        $posts = $feed->getUserFeed($user, 10, $selectedFlairIds);

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

        // Surface PUP-ITECH Official to verified members (skip if already connected/pending).
        if ($user->isVerified() && ! $user->isInstitution()) {
            $institutionSuggestion = User::query()->where('role', 'superadmin')
                ->whereNotIn('id', $excludedIds)
                ->first(['id', 'name', 'batch_year', 'program_course', 'account_status', 'role']);
            if ($institutionSuggestion) {
                $suggestedPeople = $suggestedPeople->prepend($institutionSuggestion);
            }
        }

        $joinedCommunities = $user->communities()
            ->orderBy('name')
            ->get(['communities.id', 'communities.name', 'communities.system_key']);

        // Available flairs: global and from joined communities
        $availableFlairs = \App\Models\Flair::query()
            ->whereNull('community_id')
            ->orWhereIn('community_id', $joinedCommunities->pluck('id'))
            ->get();

        // Composer picker excludes system flairs (e.g. "Event"); the feed filter keeps them.
        $flairsByCommunity = $availableFlairs
            ->where('is_system', false)
            ->groupBy(fn($f) => $f->community_id ?? 'global')
            ->map(fn($group) => $group->values()->map(fn($f) => [
                'id' => $f->id, 'name' => $f->name, 'color' => $f->color, 'icon' => $f->icon,
            ]));

        $viewData = [
            'posts' => $posts,
            'selectedFlairIds' => $selectedFlairIds,
            'featuredCommunities' => $featuredCommunities,
            'suggestedPeople' => $suggestedPeople,
            'joinedCommunities' => $joinedCommunities,
            'availableFlairs' => $availableFlairs,
            'flairsByCommunity' => $flairsByCommunity,
        ];

        // Admins get the User Management table rendered inline on their home.
        if ($user->canManageCommunities()) {
            $viewData = array_merge($viewData, UserAdminController::listData(request()));
        }

        return view('dashboard', $viewData);
    }

    return view('welcome');
});

Route::get('/dashboard', function (Request $request, FeedService $feed) {
    $user = $request->user();
    /** @var \App\Models\User $user */

    if (empty($user->first_name)) {
        return redirect()->route('register.complete');
    }

    $selectedFlairIds = array_values(array_unique(array_map('intval', array_filter((array) $request->query('flairs', [])))));
    $posts = $feed->getUserFeed($user, 10, $selectedFlairIds);

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

    // Surface PUP-ITECH Official to verified members (skip if already connected/pending).
    if ($user->isVerified() && ! $user->isInstitution()) {
        $institutionSuggestion = User::query()->where('role', 'superadmin')
            ->whereNotIn('id', $excludedIds)
            ->first(['id', 'name', 'batch_year', 'program_course', 'account_status', 'role']);
        if ($institutionSuggestion) {
            $suggestedPeople = $suggestedPeople->prepend($institutionSuggestion);
        }
    }

    $joinedCommunities = $user->communities()
        ->orderBy('name')
        ->get(['communities.id', 'communities.name', 'communities.system_key']);

    $availableFlairs = \App\Models\Flair::query()
        ->whereNull('community_id')
        ->orWhereIn('community_id', $joinedCommunities->pluck('id'))
        ->get();

    // Composer picker excludes system flairs (e.g. "Event"); the feed filter keeps them.
    $flairsByCommunity = $availableFlairs
        ->where('is_system', false)
        ->groupBy(fn($f) => $f->community_id ?? 'global')
        ->map(fn($group) => $group->values()->map(fn($f) => [
            'id' => $f->id, 'name' => $f->name, 'color' => $f->color, 'icon' => $f->icon,
        ]));

    $viewData = [
        'posts' => $posts,
        'selectedFlairIds' => $selectedFlairIds,
        'featuredCommunities' => $featuredCommunities,
        'suggestedPeople' => $suggestedPeople,
        'joinedCommunities' => $joinedCommunities,
        'availableFlairs' => $availableFlairs,
        'flairsByCommunity' => $flairsByCommunity,
    ];

    // Admins get the User Management table rendered inline on their home.
    if ($user->canManageCommunities()) {
        $viewData = array_merge($viewData, UserAdminController::listData($request));
    }

    return view('dashboard', $viewData);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/feed/posts', function (Request $request, FeedService $feed) {
    $user = $request->user();
    $selectedFlairIds = array_values(array_unique(array_map('intval', array_filter((array) $request->query('flairs', [])))));
    $posts = $feed->getUserFeed($user, 10, $selectedFlairIds);

    return response()->json([
        'html' => view('partials.feed-posts', ['posts' => $posts])->render(),
        'hasMore' => $posts->hasMorePages(),
    ]);
})->middleware('auth')->name('feed.posts');

Route::get('/communities/{community}/feed', function (Request $request, Community $community, FeedService $feed) {
    $selectedFlairIds = array_values(array_unique(array_map('intval', array_filter((array) $request->query('flairs', [])))));
    $posts = $feed->getCommunityFeed($community, $request->user(), 10, $selectedFlairIds);

    return response()->json([
        'html' => view('partials.feed-posts', ['posts' => $posts])->render(),
        'hasMore' => $posts->hasMorePages(),
    ]);
})->middleware('auth')->name('communities.feed');

Route::middleware('auth')->group(function () {
    Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');

    // Community creation requests (verified users)
    Route::get('/communities/requests/create', [CommunityCreationRequestController::class, 'create'])
        ->name('communities.requests.create');
    Route::post('/communities/requests', [CommunityCreationRequestController::class, 'store'])
        ->name('communities.requests.store');
    Route::get('/communities/requests/{communityRequest}', [CommunityCreationRequestController::class, 'show'])
        ->name('communities.requests.show');
    Route::post('/communities/requests/{communityRequest}/cancel', [CommunityCreationRequestController::class, 'cancel'])
        ->name('communities.requests.cancel');

    // Co-moderator invite responses
    Route::post('/community-invites/{invite}/accept', [CoModeratorInviteController::class, 'accept'])
        ->name('community-invites.accept');
    Route::post('/community-invites/{invite}/decline', [CoModeratorInviteController::class, 'decline'])
        ->name('community-invites.decline');

    Route::get('/communities/{community}', [CommunityController::class, 'show'])->name('communities.show');
    Route::get('/communities/{community}/members', [CommunityController::class, 'members'])->name('communities.members');
    Route::post('/communities/{community}/join', [MembershipController::class, 'join'])->name('communities.join');
    Route::delete('/communities/{community}/leave', [MembershipController::class, 'leave'])->name('communities.leave');

    // Program-batch join requests
    Route::get('/communities/{community}/join-requests', [CommunityJoinRequestController::class, 'index'])
        ->name('communities.join-requests.index');
    Route::get('/communities/{community}/manage-members', [CommunityModerationController::class, 'manageMembers'])
        ->name('communities.manage-members');
    Route::post('/communities/{community}/join-request', [CommunityJoinRequestController::class, 'store'])
        ->name('communities.join-request.store');
    Route::post('/communities/{community}/join-requests/{joinRequest}/accept', [CommunityJoinRequestController::class, 'accept'])
        ->name('communities.join-requests.accept');
    Route::post('/communities/{community}/join-requests/{joinRequest}/ignore', [CommunityJoinRequestController::class, 'ignore'])
        ->name('communities.join-requests.ignore');
    Route::delete('/communities/{community}/join-requests/{joinRequest}/withdraw', [CommunityJoinRequestController::class, 'withdraw'])
        ->name('communities.join-requests.withdraw');
    Route::get('/communities/{community}/join-requests/{joinRequest}/withdraw', function (Community $community) {
        return redirect()->route('communities.show', $community);
    });

    // Moderator actions
    Route::patch('/communities/{community}/description', [CommunityModerationController::class, 'updateDescription'])
        ->name('communities.description.update');
    Route::delete('/communities/{community}/members/{member}', [CommunityModerationController::class, 'removeMember'])
        ->name('communities.members.remove');

    // Moderator role transfer (invite-based)
    Route::post('/communities/{community}/mod-transfer/{toUser}', [CommunityModeratorTransferController::class, 'store'])
        ->name('communities.mod-transfer.store');
    Route::post('/mod-transfers/{transfer}/accept', [CommunityModeratorTransferController::class, 'accept'])
        ->name('mod-transfers.accept');
    Route::post('/mod-transfers/{transfer}/decline', [CommunityModeratorTransferController::class, 'decline'])
        ->name('mod-transfers.decline');
    Route::delete('/mod-transfers/{transfer}/cancel', [CommunityModeratorTransferController::class, 'cancel'])
        ->name('mod-transfers.cancel');
    Route::delete('/communities/{community}/mod/posts/{post}', [CommunityModerationController::class, 'deletePost'])
        ->name('communities.mod.posts.destroy');

    // Redirect legacy /posts index URL to the community page
    Route::get('/communities/{community}/posts', fn (Community $community) => redirect()->route('communities.show', $community));

    // Posts - read routes
    Route::resource('communities.posts', CommunityPostController::class)
        ->only(['show']);

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
        Route::get('/communities/{community}/posts/{post}/open', function (Community $community, $post, Request $request) {
            // Resolve manually so a hard-deleted (missing) or trashed post shows a
            // graceful "no longer available" notice instead of a 404 / a removed
            // post opening as if it were live.
            $post = Post::find($post);
            if (! $post || $post->trashed_at || (int) $post->community_id !== (int) $community->id) {
                return redirect()->route('dashboard')->with('error', 'This post is no longer available.');
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

        // Community-less (connections-only) posts: same actions without a community scope.
        Route::get('/posts/{post}/open', function ($post, Request $request) {
            $post = Post::find($post);
            if (! $post || $post->trashed_at) {
                return redirect()->route('dashboard')->with('error', 'This post is no longer available.');
            }

            $request->user()?->can('view', $post) ?: abort(403);

            return redirect()->route('dashboard')->with('openPostModal', [
                'postId' => $post->id,
                'apiUrl' => route('posts.api', ['post' => $post->id]),
                'commentsUrl' => route('posts.comments.index', ['post' => $post->id]),
            ]);
        })->name('posts.open');
        Route::get('/posts/{post}/api', [CommentController::class, 'getPostDetailsForPost'])
            ->name('posts.api');
        Route::get('/posts/{post}/comments', [CommentController::class, 'indexForPost'])
            ->name('posts.comments.index');
        Route::post('/posts/{post}/comments', [CommentController::class, 'storeForPost'])
            ->name('posts.comments.store');
        Route::delete('/posts/{post}/comments/{comment}', [CommentController::class, 'destroyForPost'])
            ->name('posts.comments.destroy');
    });

    // Likes - toggle route
    Route::post('/communities/{community}/posts/{post}/like', [PostLikeController::class, 'toggle'])
        ->middleware('auth')
        ->name('communities.posts.like');
    Route::post('/posts/{post}/like', [PostLikeController::class, 'toggleForPost'])
        ->middleware('auth')
        ->name('posts.like');

    // Post management: edit, trash, restore, permanent delete
    Route::middleware('auth')->group(function () {
        Route::patch('/posts/{post}', [PostController::class, 'update'])
            ->name('posts.update');
        Route::delete('/posts/{post}/trash', [PostTrashController::class, 'destroy'])
            ->name('posts.trash');
        // Moderator/superadmin removes someone else's post with a reason (soft,
        // non-restorable by the author) and notifies them.
        Route::post('/posts/{post}/remove', [PostModerationController::class, 'remove'])
            ->name('posts.moderate-remove');
        Route::post('/posts/{post}/restore', [PostTrashController::class, 'restore'])
            ->name('posts.restore');
        Route::delete('/posts/{post}/force', [PostTrashController::class, 'forceDelete'])
            ->name('posts.force-delete');
        Route::get('/profile/trash', [PostTrashController::class, 'index'])
            ->name('profile.post-trash');

        // Abuse reports — any verified user in a post's audience can report it.
        Route::post('/posts/{post}/report', [PostReportController::class, 'store'])
            ->name('posts.report');
    });
    // Route::get('/communities/{community}/posts/{post}', [PostController::class, 'show'])->name('communities.posts.show');

    // // Posts - create/edit/delete (requires membership)
    // Route::middleware('ensure.community.member')->resource('communities.posts', CommunityPostController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profiles/{user}', [ProfileController::class, 'show'])->name('profiles.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/onboarding-step', [ProfileController::class, 'onboardingStep'])->name('profile.onboarding-step');
    Route::post('/profile/onboarding-complete', [ProfileController::class, 'onboardingComplete'])->name('profile.onboarding-complete');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/verification', [VerificationController::class, 'store'])->name('verification.store');

    // Direct messaging (1-on-1, accepted connections only). The unread-count and
    // index routes are declared before the numeric {user} route to avoid collision.
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unread-count');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->whereNumber('user')->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->whereNumber('user')->name('messages.store');
    Route::post('/messages/{user}/read', [MessageController::class, 'markRead'])->whereNumber('user')->name('messages.read');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/trash', [NotificationController::class, 'trash'])->name('notifications.trash');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/star-many', [NotificationController::class, 'starMany'])->name('notifications.star-many');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/{notification}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.unread');
    Route::post('/notifications/{notification}/star', [NotificationController::class, 'toggleStar'])->name('notifications.star');
    Route::post('/notifications/{notification}/restore', [NotificationController::class, 'restore'])->name('notifications.restore');
    Route::post('/notifications/trash/empty', [NotificationController::class, 'emptyTrash'])->name('notifications.trash.empty');
    Route::delete('/notifications/{notification}/force', [NotificationController::class, 'forceDestroy'])->name('notifications.force-destroy');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/connections', [ConnectionController::class, 'index'])->name('connections.index');
    Route::get('/connections/counts', [ConnectionController::class, 'counts'])->name('connections.counts');
    Route::post('/connections/invite/{user}', [ConnectionController::class, 'invite'])->name('connections.invite');
    Route::post('/connections/{connection}/accept', [ConnectionController::class, 'accept'])->name('connections.accept');
    Route::post('/connections/{connection}/ignore', [ConnectionController::class, 'ignore'])->name('connections.ignore');
    Route::delete('/connections/{connection}/withdraw', [ConnectionController::class, 'withdraw'])->name('connections.withdraw');
    Route::delete('/connections/{connection}/remove', [ConnectionController::class, 'remove'])->name('connections.remove');

    
});

// Exit the institution "act as" mode and return to the original admin. Only
// usable while a switch is active (the controller checks the session marker);
// not admin-gated because the institution account itself isn't an admin.
Route::post('/exit-institution', [InstitutionSwitchController::class, 'exit'])
    ->middleware('auth')->name('institution.exit');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/inbox', [AdminInboxController::class, 'index'])->name('inbox');
    Route::get('/inbox/counts', [AdminInboxController::class, 'counts'])->name('inbox.counts');

    Route::get('/communities', [CommunityAdminController::class, 'index'])->name('communities.index');
    Route::post('/communities', [CommunityAdminController::class, 'store'])->name('communities.store');
    Route::patch('/communities/{community}', [CommunityAdminController::class, 'update'])->name('communities.update');
    Route::delete('/communities/{community}', [CommunityAdminController::class, 'destroy'])->name('communities.destroy');

    Route::resource('flairs', FlairAdminController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::get('/community-requests', [CommunityCreationRequestAdminController::class, 'index'])
        ->name('community-requests.index');
    Route::get('/community-requests/{communityRequest}', [CommunityCreationRequestAdminController::class, 'show'])
        ->name('community-requests.show');
    Route::post('/community-requests/{communityRequest}/approve', [CommunityCreationRequestAdminController::class, 'approve'])
        ->name('community-requests.approve');
    Route::post('/community-requests/{communityRequest}/reject', [CommunityCreationRequestAdminController::class, 'reject'])
        ->name('community-requests.reject');

    // Switch into the PUP-ITECH Official institution account ("act as")
    Route::post('/act-as-institution', [InstitutionSwitchController::class, 'enter'])->name('institution.enter');

    // Reported posts review queue
    Route::get('/reports', [PostReportAdminController::class, 'index'])->name('reports.index');
    Route::post('/reports/{post}/keep', [PostReportAdminController::class, 'keep'])->name('reports.keep');
    Route::delete('/reports/{post}', [PostReportAdminController::class, 'destroy'])->name('reports.destroy');

    Route::get('/verifications', [VerificationAdminController::class, 'index'])->name('verifications.index');
    Route::get('/verifications/{verificationDocument}/document', [VerificationAdminController::class, 'viewDocument'])->name('verifications.document');
    Route::patch('/verifications/{verificationDocument}/approve', [VerificationAdminController::class, 'approve'])->name('verifications.approve');
    Route::patch('/verifications/{verificationDocument}/reject', [VerificationAdminController::class, 'reject'])->name('verifications.reject');

    Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
    Route::get('/users/stats', [UserAdminController::class, 'stats'])->name('users.stats');
    Route::patch('/users/{user}', [UserAdminController::class, 'update'])->name('users.update');
});

require __DIR__ . '/auth.php';
