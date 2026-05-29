<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityJoinRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $communities = Community::query()
            ->withCount('members')
            ->with('rules')
            ->orderBy('name')
            ->get();

        $user->loadMissing('communities');

        return view('communities.index', [
            'communities' => $communities,
            'memberCommunityIds' => $user->communities->modelKeys(),
            'user' => $user,
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
