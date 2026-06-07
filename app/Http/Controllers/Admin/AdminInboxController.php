<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityCreationRequest;
use App\Models\Post;
use App\Models\VerificationDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminInboxController extends Controller
{
    public function index(): View
    {
        $pendingCommunityRequests = CommunityCreationRequest::query()
            ->pendingAdmin()
            ->with(['requestor', 'coModeratorInvites.invitedUser'])
            ->latest()
            ->get();

        $pendingVerifications = VerificationDocument::query()
            ->where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        $reportedPosts = Post::query()
            ->whereNotNull('flagged_at')
            ->whereNull('trashed_at')
            ->with(['user', 'community'])
            ->orderByDesc('flagged_at')
            ->get();

        return view('admin.inbox', [
            'pendingCommunityRequests' => $pendingCommunityRequests,
            'pendingVerifications' => $pendingVerifications,
            'reportedPosts' => $reportedPosts,
        ]);
    }

    public function counts(): JsonResponse
    {
        $communityRequests = CommunityCreationRequest::pendingAdmin()->count();
        $verifications = VerificationDocument::where('status', 'pending')->count();
        $reportedPosts = Post::whereNotNull('flagged_at')->whereNull('trashed_at')->count();

        return response()->json([
            'community_requests' => $communityRequests,
            'verifications' => $verifications,
            'reported_posts' => $reportedPosts,
            'total' => $communityRequests + $verifications + $reportedPosts,
        ]);
    }
}
