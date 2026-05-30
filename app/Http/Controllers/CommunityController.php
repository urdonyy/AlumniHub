<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityCreationRequest;
use App\Models\CommunityJoinRequest;
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

    public function show(Request $request, Community $community): View
    {
        $community->loadCount('members');
        $community->load([
            'creator',
            'rules',
            'members' => function ($query) {
                $query->orderBy('name')->limit(12);
            },
        ]);

        $user = $request->user();
        $user->loadMissing('communities');

        $isMember = $user->communities->contains('id', $community->id);
        $isModerator = $community->isModerator($user);
        $isAdmin = $user->canManageCommunities();
        $canModerate = $isModerator || $isAdmin;

        $pendingJoinRequest = null;
        $pendingJoinRequests = collect();
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
                $pendingJoinRequests = CommunityJoinRequest::query()
                    ->with('user')
                    ->where('community_id', $community->id)
                    ->where('status', CommunityJoinRequest::STATUS_PENDING)
                    ->latest()
                    ->get();
            }
        }

        return view('communities.show', [
            'community' => $community,
            'isMember' => $isMember,
            'canInteract' => $user->canInteractInCommunities(),
            'isVerified' => $user->isVerified(),
            'isModerator' => $isModerator,
            'isAdmin' => $isAdmin,
            'canModerate' => $canModerate,
            'pendingJoinRequest' => $pendingJoinRequest,
            'pendingJoinRequests' => $pendingJoinRequests,
            'otherProgramBatch' => $otherProgramBatch,
            'user' => $user,
        ]);
    }
}
