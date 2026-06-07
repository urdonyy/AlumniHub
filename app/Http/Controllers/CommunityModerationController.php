<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityModeratorTransfer;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommunityMemberRemovedNotification;
use App\Notifications\CommunityPostRemovedNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityModerationController extends Controller
{
    use AuthorizesRequests;

    public function manageMembers(Request $request, Community $community): View
    {
        abort_unless($community->isProgramBatch(), 404);
        $this->authorizeMod($request, $community);

        $user = $request->user();
        $isModerator = $community->isModerator($user);
        $isAdmin = $user->canManageCommunities();

        $community->load(['members' => fn ($q) => $q->orderBy('name')]);

        $myPendingTransfers = $isModerator
            ? CommunityModeratorTransfer::where('community_id', $community->id)
                ->where('from_user_id', $user->id)
                ->where('status', CommunityModeratorTransfer::STATUS_PENDING)
                ->get()
                ->keyBy('to_user_id')
            : collect();

        return view('communities.manage-members', [
            'community' => $community,
            'user' => $user,
            'isModerator' => $isModerator,
            'isAdmin' => $isAdmin,
            'myPendingTransfers' => $myPendingTransfers,
        ]);
    }

    public function updateDescription(Request $request, Community $community): RedirectResponse
    {
        $this->authorizeMod($request, $community);

        $validated = $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $community->update(['description' => $validated['description']]);

        return back()->with('status', 'description-updated');
    }

    public function removeMember(Request $request, Community $community, User $member): RedirectResponse
    {
        $this->authorizeMod($request, $community);

        if ($member->id === $request->user()->id) {
            return back()->withErrors(['member' => 'You cannot remove yourself this way. Use Leave Community instead.']);
        }

        if ($community->isModerator($member) && ! $request->user()->canManageCommunities()) {
            return back()->withErrors(['member' => 'Moderators cannot remove other moderators.']);
        }

        if (! $community->members()->whereKey($member->id)->exists()) {
            return back()->withErrors(['member' => 'User is not a member of this community.']);
        }

        $community->members()->detach($member->id);
        $member->notify(new CommunityMemberRemovedNotification($community, $request->user()));

        return back()->with('status', 'member-removed');
    }

    public function deletePost(Request $request, Community $community, Post $post): RedirectResponse
    {
        $this->authorizeMod($request, $community);

        abort_unless($post->community_id === $community->id, 404);

        $author = $post->user;
        $post->delete();

        if ($author && $author->id !== $request->user()->id) {
            $author->notify(new CommunityPostRemovedNotification($community, $post, $request->user()));
        }

        return redirect()
            ->route('communities.show', $community)
            ->with('status', 'post-removed');
    }

    private function authorizeMod(Request $request, Community $community): void
    {
        $user = $request->user();
        if (! $user->canManageCommunities() && ! $community->isModerator($user)) {
            abort(403);
        }
    }
}
