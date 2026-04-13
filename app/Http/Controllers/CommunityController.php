<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $communities = Community::query()
            ->withCount('members')
            ->with('rules')
            ->orderBy('name')
            ->get();

        $user->loadMissing('communities');

        return view('communities.index', [
            'communities' => $communities,
            'memberCommunityIds' => $user->communities->modelKeys(),
            'user' => $user,
        ]);
    }

    public function show(Request $request, Community $community): View
    {
        $community->loadCount('members');
        $community->load([
            'creator',
            'rules',
            'members' => function ($query) {
                $query->orderBy('name')->limit(12);
            },
        ]);

        $user = $request->user();
        $user->loadMissing('communities');

        return view('communities.show', [
            'community' => $community,
            'isMember' => $user->communities->contains('id', $community->id),
            'canInteract' => $user->canInteractInCommunities(),
            'user' => $user,
        ]);
    }
}
