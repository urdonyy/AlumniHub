<?php

namespace App\Http\Middleware;

use App\Models\Community;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommunityMember
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $community = $request->route('community');

        // If no community in route, allow through
        if (!$community) {
            return $next($request);
        }

        // If community is not found, return 404
        if (!$community instanceof Community) {
            abort(404, 'Community not found');
        }

        // Check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user is a community member
        if (!$community->members()->where('user_id', $request->user()->id)->exists()) {
            abort(403, 'You must be a member of this community to perform this action');
        }

        return $next($request);
    }
}
