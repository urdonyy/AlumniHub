<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityModeratorTransfer;
use App\Models\User;
use App\Notifications\ModeratorTransferInviteNotification;
use App\Notifications\ModeratorTransferRespondedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityModeratorTransferController extends Controller
{
    public function store(Request $request, Community $community, User $toUser): RedirectResponse
    {
        $user = $request->user();

        abort_unless($community->isModerator($user), 403);

        if ($toUser->id === $user->id) {
            return back()->withErrors(['member' => 'You cannot transfer your role to yourself.']);
        }

        if (! $community->members()->whereKey($toUser->id)->exists()) {
            return back()->withErrors(['member' => 'That user is not a member of this community.']);
        }

        if ($community->isModerator($toUser)) {
            return back()->withErrors(['member' => 'That user is already a moderator.']);
        }

        // Cancel any existing pending transfer from this moderator in this community
        CommunityModeratorTransfer::where('community_id', $community->id)
            ->where('from_user_id', $user->id)
            ->where('status', CommunityModeratorTransfer::STATUS_PENDING)
            ->update(['status' => CommunityModeratorTransfer::STATUS_CANCELLED]);

        $role = $community->moderators()->where('user_id', $user->id)->value('role') ?? 'moderator';

        $transfer = CommunityModeratorTransfer::create([
            'community_id' => $community->id,
            'from_user_id' => $user->id,
            'to_user_id'   => $toUser->id,
            'role'         => $role,
            'status'       => CommunityModeratorTransfer::STATUS_PENDING,
        ]);

        $toUser->notify(new ModeratorTransferInviteNotification($transfer, $user));

        return back()->with('status', 'transfer-invite-sent');
    }

    public function accept(Request $request, CommunityModeratorTransfer $transfer): RedirectResponse
    {
        abort_unless($transfer->to_user_id === $request->user()->id, 403);
        abort_unless($transfer->isPending(), 422);

        DB::transaction(function () use ($transfer) {
            $transfer->community->moderators()->where('user_id', $transfer->from_user_id)->delete();
            $transfer->community->addModerator($transfer->toUser, $transfer->role);
            $transfer->community->members()->syncWithoutDetaching([$transfer->to_user_id]);

            // Clean up any pending join request the new mod may have had
            CommunityJoinRequest::where('community_id', $transfer->community_id)
                ->where('user_id', $transfer->to_user_id)
                ->where('status', CommunityJoinRequest::STATUS_PENDING)
                ->delete();

            $transfer->update([
                'status'       => CommunityModeratorTransfer::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);

            $transfer->fromUser->notify(new ModeratorTransferRespondedNotification($transfer, true));
        });

        return redirect()
            ->route('communities.show', $transfer->community_id)
            ->with('status', 'transfer-accepted');
    }

    public function decline(Request $request, CommunityModeratorTransfer $transfer): RedirectResponse
    {
        abort_unless($transfer->to_user_id === $request->user()->id, 403);
        abort_unless($transfer->isPending(), 422);

        $transfer->update([
            'status'       => CommunityModeratorTransfer::STATUS_DECLINED,
            'responded_at' => now(),
        ]);

        $transfer->fromUser->notify(new ModeratorTransferRespondedNotification($transfer, false));

        return redirect()
            ->route('communities.show', $transfer->community_id)
            ->with('status', 'transfer-declined');
    }

    public function cancel(Request $request, CommunityModeratorTransfer $transfer): RedirectResponse
    {
        abort_unless($transfer->from_user_id === $request->user()->id, 403);
        abort_unless($transfer->isPending(), 422);

        $transfer->update(['status' => CommunityModeratorTransfer::STATUS_CANCELLED]);

        return back()->with('status', 'transfer-cancelled');
    }
}
