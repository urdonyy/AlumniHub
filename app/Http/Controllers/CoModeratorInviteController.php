<?php

namespace App\Http\Controllers;

use App\Models\CommunityCreationRequestModerator;
use App\Services\CommunityCreationRequestService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class CoModeratorInviteController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly CommunityCreationRequestService $service) {}

    public function accept(CommunityCreationRequestModerator $invite): RedirectResponse
    {
        $this->authorize('respondAsCoMod', $invite);

        $this->service->respondAsCoMod($invite, true);

        return redirect()
            ->route('communities.requests.show', $invite->request_id)
            ->with('status', 'co-moderator-accepted');
    }

    public function decline(CommunityCreationRequestModerator $invite): RedirectResponse
    {
        $this->authorize('respondAsCoMod', $invite);

        $this->service->respondAsCoMod($invite, false);

        return redirect()
            ->route('communities.requests.show', $invite->request_id)
            ->with('status', 'co-moderator-declined');
    }
}
