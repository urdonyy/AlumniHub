<?php

namespace App\Services;

use App\Exceptions\UserAlreadyInProgramBatchException;
use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityModeratorTransfer;
use App\Models\User;
use App\Notifications\JoinRequestAcceptedNotification;
use App\Notifications\JoinRequestIgnoredNotification;
use App\Notifications\JoinRequestSubmittedNotification;
use Illuminate\Support\Facades\DB;

class CommunityJoinRequestService
{
    public function request(User $user, Community $community): CommunityJoinRequest
    {
        return DB::transaction(function () use ($user, $community) {
            $jr = CommunityJoinRequest::updateOrCreate(
                ['community_id' => $community->id, 'user_id' => $user->id],
                [
                    'status' => CommunityJoinRequest::STATUS_PENDING,
                    'responded_by' => null,
                    'responded_at' => null,
                ],
            );

            $moderators = $community->moderators()->with('user')->get();
            foreach ($moderators as $modRow) {
                $modRow->user?->notify(new JoinRequestSubmittedNotification($jr, $community, $user));
            }

            return $jr;
        });
    }

    public function accept(CommunityJoinRequest $jr, User $mod): void
    {
        $jr->loadMissing(['community', 'user']);

        $existing = $jr->user->programBatchCommunity();
        if ($existing && $existing->id !== $jr->community_id) {
            throw new UserAlreadyInProgramBatchException($jr->user, $existing);
        }

        DB::transaction(function () use ($jr, $mod) {
            $jr->community->members()->syncWithoutDetaching([$jr->user_id]);

            // Clear any stale pending transfer offers so they don't carry over from a previous membership
            CommunityModeratorTransfer::where('community_id', $jr->community_id)
                ->where('to_user_id', $jr->user_id)
                ->where('status', CommunityModeratorTransfer::STATUS_PENDING)
                ->update(['status' => CommunityModeratorTransfer::STATUS_CANCELLED]);

            $jr->update([
                'status' => CommunityJoinRequest::STATUS_ACCEPTED,
                'responded_by' => $mod->id,
                'responded_at' => now(),
            ]);

            $jr->user->notify(new JoinRequestAcceptedNotification($jr->community));
        });
    }

    public function ignore(CommunityJoinRequest $jr, User $mod): void
    {
        $jr->loadMissing(['community', 'user']);

        DB::transaction(function () use ($jr, $mod) {
            $jr->update([
                'status' => CommunityJoinRequest::STATUS_IGNORED,
                'responded_by' => $mod->id,
                'responded_at' => now(),
            ]);

            $jr->user->notify(new JoinRequestIgnoredNotification($jr->community));
        });
    }
}
