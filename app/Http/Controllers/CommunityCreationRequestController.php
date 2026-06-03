<?php

namespace App\Http\Controllers;

use App\Exceptions\CommunityNameTakenException;
use App\Http\Requests\StoreCommunityCreationRequest;
use App\Models\CommunityCreationRequest;
use App\Models\Connection;
use App\Models\User;
use App\Services\CommunityCreationRequestService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityCreationRequestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly CommunityCreationRequestService $service) {}

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', CommunityCreationRequest::class);

        $user = $request->user();

        $activeRequest = CommunityCreationRequest::query()
            ->where('requestor_id', $user->id)
            ->whereIn('status', [
                CommunityCreationRequest::STATUS_PENDING_CO_MODS,
                CommunityCreationRequest::STATUS_PENDING_ADMIN,
            ])
            ->latest()
            ->first();

        if ($activeRequest) {
            return redirect()
                ->route('communities.requests.show', $activeRequest)
                ->with('status', 'community-request-already-active');
        }

        $connections = Connection::query()
            ->with(['sender', 'recipient'])
            ->where('status', Connection::STATUS_ACCEPTED)
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
            })
            ->get()
            ->map(fn (Connection $c) => $c->otherPartyFor($user))
            ->filter(fn (?User $u) => $u !== null
                && $u->isVerified()
                && $u->batch_year === $user->batch_year
                && $u->program_course === $user->program_course)
            ->unique('id')
            ->values();

        return view('communities.requests.create', [
            'user' => $user,
            'connections' => $connections,
            'existingCommunity' => session('existing_community'),
        ]);
    }

    public function store(StoreCommunityCreationRequest $request): RedirectResponse
    {
        try {
            $created = $this->service->submit(
                $request->user(),
                $request->validatedData(),
                array_values(array_map('intval', $request->input('co_moderator_ids', []))),
            );
        } catch (CommunityNameTakenException $e) {
            return back()
                ->withInput()
                ->with('existing_community', $e->existingCommunity)
                ->withErrors(['name' => 'A community with this name already exists. Join it instead.']);
        }

        return redirect()
            ->route('communities.requests.show', $created)
            ->with('status', 'community-request-submitted');
    }

    public function show(Request $request, CommunityCreationRequest $communityRequest): View
    {
        $this->authorize('view', $communityRequest);

        $communityRequest->load([
            'requestor',
            'coModeratorInvites.invitedUser',
            'community',
            'admin',
        ]);

        $user = $request->user();
        $myInvite = $communityRequest->coModeratorInvites
            ->firstWhere('invited_user_id', $user->id);

        return view('communities.requests.show', [
            'communityRequest' => $communityRequest,
            'myInvite' => $myInvite,
            'user' => $user,
        ]);
    }

    public function cancel(CommunityCreationRequest $communityRequest): RedirectResponse
    {
        $this->authorize('cancel', $communityRequest);

        $this->service->cancelByRequestor($communityRequest);

        return redirect()
            ->route('communities.requests.show', $communityRequest)
            ->with('status', 'community-request-cancelled');
    }
}
