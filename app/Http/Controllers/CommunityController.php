<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityCreationRequest;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityModeratorTransfer;
use App\Models\Flair;
use App\Models\Post;
use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('communities');
        $memberCommunityIds = $user->communities->modelKeys();

        $allCommunities = Community::query()
            ->withCount('members')
            ->with('rules')
            ->get();

        // Left column: system communities ordered — general → joined program → others
        $systemCommunities = $allCommunities->where('is_system', true);

        $general = $systemCommunities->firstWhere('system_key', 'general-alumni-hub');

        $joinedProgram = $systemCommunities
            ->where('system_key', '!=', 'general-alumni-hub')
            ->first(fn ($c) => in_array($c->id, $memberCommunityIds, true));

        $otherSystem = $systemCommunities
            ->filter(fn ($c) =>
                $c->system_key !== 'general-alumni-hub' &&
                (! $joinedProgram || $c->id !== $joinedProgram->id)
            )
            ->sortBy('name')
            ->values();

        $orderedSystemCommunities = collect();
        if ($general) $orderedSystemCommunities->push($general);
        if ($joinedProgram) $orderedSystemCommunities->push($joinedProgram);
        $orderedSystemCommunities = $orderedSystemCommunities->merge($otherSystem);

        // Right column: alumni-requested batch communities (approved, not system)
        $createdBatchCommunities = $allCommunities
            ->where('is_system', false)
            ->where('type', Community::TYPE_PROGRAM_BATCH)
            ->sortBy('name')
            ->values();

        $activeCreationRequest = CommunityCreationRequest::query()
            ->where('requestor_id', $user->id)
            ->whereIn('status', [
                CommunityCreationRequest::STATUS_PENDING_CO_MODS,
                CommunityCreationRequest::STATUS_PENDING_ADMIN,
            ])
            ->latest()
            ->first();

        return view('communities.index', [
            'systemCommunities' => $orderedSystemCommunities,
            'createdBatchCommunities' => $createdBatchCommunities,
            'memberCommunityIds' => $memberCommunityIds,
            'user' => $user,
            'activeCreationRequest' => $activeCreationRequest,
        ]);
    }

    public function show(Request $request, Community $community, FeedService $feed): View
    {
        $community->loadCount('members');
        $community->load(['creator', 'rules']);

        $user = $request->user();
        $user->loadMissing('communities');

        $isMember = $user->communities->contains('id', $community->id);
        $isModerator = $community->isModerator($user);
        $isAdmin = $user->canManageCommunities();
        $canModerate = $isModerator || $isAdmin;

        $pendingJoinRequest = null;
        $pendingJoinRequestCount = 0;
        $otherProgramBatch = null;

        if ($community->isProgramBatch()) {
            if (! $isMember) {
                $pendingJoinRequest = CommunityJoinRequest::query()
                    ->where('community_id', $community->id)
                    ->where('user_id', $user->id)
                    ->where('status', CommunityJoinRequest::STATUS_PENDING)
                    ->first();

                $otherProgramBatch = $user->programBatchCommunity();
            }

            if ($canModerate) {
                $pendingJoinRequestCount = CommunityJoinRequest::where('community_id', $community->id)
                    ->where('status', CommunityJoinRequest::STATUS_PENDING)
                    ->count();
            }
        }

        // Activity feed: members (and admins) see every post in the community,
        // everyone else only sees public posts. Pulse-ranked, paginated, with
        // an optional flair filter — same engine as the dashboard feed.
        $selectedFlairIds = array_values(array_unique(array_map('intval', array_filter((array) $request->query('flairs', [])))));
        $communityFeed = $feed->getCommunityFeed($community, $user, 10, $selectedFlairIds);

        // Members (incl. unverified) see all posts; non-members see public only.
        $canSeeAllPosts = $isMember || $isAdmin;
        $postCount = Post::query()
            ->where('status', 'published')
            ->whereNull('trashed_at')
            ->where('community_id', $community->id)
            ->when(! $canSeeAllPosts, fn ($query) => $query->where('visibility', 'public'))
            ->count();

        // Flairs available for the filter (global + this community's), and the
        // composer picker (selectable flairs only — system flairs like "Event" excluded).
        $availableFlairs = Flair::query()
            ->whereNull('community_id')
            ->orWhere('community_id', $community->id)
            ->get();

        $flairsByCommunity = $availableFlairs
            ->where('is_system', false)
            ->groupBy(fn ($f) => $f->community_id ?? 'global')
            ->map(fn ($group) => $group->values()->map(fn ($f) => [
                'id' => $f->id, 'name' => $f->name, 'color' => $f->color, 'icon' => $f->icon,
            ]));

        // Incoming transfer invite — only relevant if the user is still a member
        $pendingTransferToMe = $isMember
            ? CommunityModeratorTransfer::where('community_id', $community->id)
                ->where('to_user_id', $user->id)
                ->where('status', CommunityModeratorTransfer::STATUS_PENDING)
                ->with('fromUser')
                ->first()
            : null;

        return view('communities.show', [
            'community' => $community,
            'communityFeed' => $communityFeed,
            'postCount' => $postCount,
            'availableFlairs' => $availableFlairs,
            'flairsByCommunity' => $flairsByCommunity,
            'selectedFlairIds' => $selectedFlairIds,
            'isMember' => $isMember,
            'canInteract' => $user->canInteractInCommunities(),
            'isVerified' => $user->isVerified(),
            'isModerator' => $isModerator,
            'isAdmin' => $isAdmin,
            'canModerate' => $canModerate,
            'pendingJoinRequest' => $pendingJoinRequest,
            'pendingJoinRequestCount' => $pendingJoinRequestCount,
            'otherProgramBatch' => $otherProgramBatch,
            'pendingTransferToMe' => $pendingTransferToMe,
            'user' => $user,
        ]);
    }

    /**
     * Full member list for the community members modal (AJAX, loaded on open).
     * Alphabetical, scrollable (not paginated). Visible to verified users only.
     */
    public function members(Request $request, Community $community): JsonResponse
    {
        abort_unless($request->user()->isVerified(), 403);

        $members = $community->members()
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.program_course', 'users.batch_year', 'users.avatar_path', 'users.role']);

        $modRoles = $community->moderators()->pluck('role', 'user_id');

        return response()->json([
            'html' => view('partials.community-members', ['members' => $members, 'modRoles' => $modRoles])->render(),
            'count' => $members->count(),
        ]);
    }
}
