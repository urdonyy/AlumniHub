<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Services\CommunityJoinRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function join(Request $request, Community $community, CommunityJoinRequestService $joinRequests): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->canInteractInCommunities(), 403);

        if ($community->isProgramBatch()) {
            if ($community->members()->whereKey($user->id)->exists()) {
                return back()->with('status', 'already-a-member');
            }
            $joinRequests->request($user, $community);
            return back()->with('status', 'join-request-submitted');
        }

        $user->communities()->syncWithoutDetaching([$community->id]);

        return back()->with('status', 'community-joined');
    }

    public function leave(Request $request, Community $community): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->canInteractInCommunities(), 403);

        if ($user->communities()->whereKey($community->id)->exists()) {
            $user->communities()->detach($community->id);
        }

        return back()->with('status', 'community-left');
    }
}
