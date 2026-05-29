<?php

namespace App\Http\Controllers;

use App\Exceptions\UserAlreadyInProgramBatchException;
use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Services\CommunityJoinRequestService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommunityJoinRequestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly CommunityJoinRequestService $service) {}

    public function store(Request $request, Community $community): RedirectResponse
    {
        abort_unless($request->user()->isVerified(), 403);
        abort_unless($community->isProgramBatch(), 404);

        if ($community->members()->whereKey($request->user()->id)->exists()) {
            return back()->with('status', 'already-a-member');
        }

        $this->service->request($request->user(), $community);

        return back()->with('status', 'join-request-submitted');
    }

    public function accept(Request $request, Community $community, CommunityJoinRequest $joinRequest): RedirectResponse
    {
        $this->authorizeMod($request, $community);
        abort_unless($joinRequest->community_id === $community->id, 404);

        try {
            $this->service->accept($joinRequest, $request->user());
        } catch (UserAlreadyInProgramBatchException $e) {
            return back()->withErrors([
                'join_request' => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'join-request-accepted');
    }

    public function ignore(Request $request, Community $community, CommunityJoinRequest $joinRequest): RedirectResponse
    {
        $this->authorizeMod($request, $community);
        abort_unless($joinRequest->community_id === $community->id, 404);

        $this->service->ignore($joinRequest, $request->user());

        return back()->with('status', 'join-request-ignored');
    }

    private function authorizeMod(Request $request, Community $community): void
    {
        $user = $request->user();
        if (! $user->canManageCommunities() && ! $community->isModerator($user)) {
            abort(403);
        }
    }
}
