<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function join(Request $request, Community $community): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->canInteractInCommunities(), 403);

        $user->communities()->syncWithoutDetaching([$community->id]);

        return back()->with('status', 'community-joined');
    }

    public function leave(Request $request, Community $community): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->canInteractInCommunities(), 403);

        if ($user->communities()->whereKey($community->id)->exists()) {
            $user->communities()->detach($community->id);
        }

        return back()->with('status', 'community-left');
    }
}
