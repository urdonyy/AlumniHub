<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityModeratorTransfer;
use App\Notifications\ModeratorTransferCancelledNotification;
use App\Services\CommunityJoinRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function join(Request $request, Community $community, CommunityJoinRequestService $joinRequests): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->canInteractInCommunities(), 403);

        if ($community->is_system) {
            return back()->with('status', 'system-community-locked');
        }

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

        if ($community->is_system) {
            return back()->with('status', 'system-community-locked');
        }

        if ($community->isProgramBatch() && $community->isModerator($user)) {
            return back()->with('status', 'co-mod-cannot-leave');
        }

        if ($user->communities()->whereKey($community->id)->exists()) {
            $user->communities()->detach($community->id);
        }

        // Cancel any pending incoming transfer invite for this community and notify the moderator
        $pendingTransfer = CommunityModeratorTransfer::where('community_id', $community->id)
            ->where('to_user_id', $user->id)
            ->where('status', CommunityModeratorTransfer::STATUS_PENDING)
            ->with(['fromUser', 'community'])
            ->first();

        if ($pendingTransfer) {
            $pendingTransfer->update(['status' => CommunityModeratorTransfer::STATUS_CANCELLED]);
            $pendingTransfer->fromUser->notify(new ModeratorTransferCancelledNotification($pendingTransfer, $user));
        }

        return back()->with('status', 'community-left');
    }
}
