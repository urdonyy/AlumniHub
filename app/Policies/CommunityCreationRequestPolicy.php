<?php

namespace App\Policies;

use App\Models\CommunityCreationRequest;
use App\Models\CommunityCreationRequestModerator;
use App\Models\User;

class CommunityCreationRequestPolicy
{
    public function create(User $user): bool
    {
        // The institution account moderates communities but can't request the
        // creation of batch communities — that's a client-side action.
        return $user->isVerified() && ! $user->isInstitution();
    }

    public function view(User $user, CommunityCreationRequest $request): bool
    {
        if ($user->canManageCommunities()) {
            return true;
        }

        if ($user->id === $request->requestor_id) {
            return true;
        }

        return $request->coModeratorInvites()
            ->where('invited_user_id', $user->id)
            ->exists();
    }

    public function respondAsCoMod(User $user, CommunityCreationRequestModerator $invite): bool
    {
        return $invite->invited_user_id === $user->id && $invite->isPending();
    }

    public function cancel(User $user, CommunityCreationRequest $request): bool
    {
        return $user->id === $request->requestor_id
            && $request->status === CommunityCreationRequest::STATUS_PENDING_CO_MODS;
    }
}
