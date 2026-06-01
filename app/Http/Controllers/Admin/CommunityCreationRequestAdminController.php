<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityCreationRequest;
use App\Services\CommunityCreationRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityCreationRequestAdminController extends Controller
{
    public function __construct(private readonly CommunityCreationRequestService $service) {}

    public function index(): View
    {
        $requests = CommunityCreationRequest::query()
            ->pendingAdmin()
            ->with(['requestor', 'coModeratorInvites.invitedUser'])
            ->latest()
            ->paginate(15);

        $history = CommunityCreationRequest::query()
            ->whereIn('status', [
                CommunityCreationRequest::STATUS_APPROVED,
                CommunityCreationRequest::STATUS_REJECTED,
            ])
            ->with(['requestor', 'admin', 'community'])
            ->latest('decided_at')
            ->limit(20)
            ->get();

        return view('admin.community-requests.index', [
            'requests' => $requests,
            'history' => $history,
        ]);
    }

    public function show(CommunityCreationRequest $communityRequest): View
    {
        $communityRequest->load([
            'requestor',
            'coModeratorInvites.invitedUser',
            'community',
            'admin',
        ]);

        return view('admin.community-requests.show', [
            'communityRequest' => $communityRequest,
        ]);
    }

    public function approve(Request $request, CommunityCreationRequest $communityRequest): RedirectResponse
    {
        abort_unless($communityRequest->status === CommunityCreationRequest::STATUS_PENDING_ADMIN, 422);

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->service->approve($communityRequest, $request->user(), $validated['admin_note'] ?? null);

        return redirect()
            ->route('admin.community-requests.index')
            ->with('status', 'community-request-approved');
    }

    public function reject(Request $request, CommunityCreationRequest $communityRequest): RedirectResponse
    {
        abort_unless($communityRequest->status === CommunityCreationRequest::STATUS_PENDING_ADMIN, 422);

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $this->service->reject($communityRequest, $request->user(), $validated['admin_note']);

        return redirect()
            ->route('admin.community-requests.index')
            ->with('status', 'community-request-rejected');
    }
}
