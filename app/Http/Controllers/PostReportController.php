<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * File an abuse report against a post.
     *
     * Any verified user who can see the post (i.e. is in its audience) may
     * report it — except the author, who can't report their own post. One
     * report per user per post. When a post collects PostReport::THRESHOLD
     * distinct pending reports it's flagged into the admin review queue; it
     * stays visible to the community until an admin decides.
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        // Must be able to see the post (audience gate) ...
        $this->authorize('view', $post);

        // ... be verified ...
        if (! $user->isVerified()) {
            return response()->json(['error' => 'Only verified members can report posts.'], 403);
        }

        // ... and not be the author.
        if ($user->id === $post->user_id) {
            return response()->json(['error' => 'You cannot report your own post.'], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', Rule::in(array_keys(PostReport::REASONS))],
            'details' => ['nullable', 'string', 'max:500'],
        ]);

        // One *open* report per user per post: block only while a previous report
        // is still unresolved. Once an admin has reviewed it (kept or removed),
        // the user may report again if the post re-offends.
        $alreadyReported = $post->reports()
            ->where('user_id', $user->id)
            ->pending()
            ->exists();
        if ($alreadyReported) {
            return response()->json([
                'success' => true,
                'already_reported' => true,
                'message' => 'You have already reported this post. Our team will review it.',
            ]);
        }

        // A UNIQUE(post_id, user_id) constraint means each user has at most one
        // report row per post. Re-reporting after an admin resolution reopens
        // that same row (rather than inserting a duplicate, which would violate
        // the constraint): clear the resolution and apply the new reason/details.
        $post->reports()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?? null,
                'resolved_at' => null,
                'resolution' => null,
            ]
        );

        // Refresh the denormalized pending count and flag for review at threshold.
        $pendingCount = $post->reports()->pending()->count();
        $post->reports_count = $pendingCount;
        if ($pendingCount >= PostReport::THRESHOLD && $post->flagged_at === null) {
            $post->flagged_at = now();
        }
        $post->save();

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your report has been submitted. Together let\'s keep AlumniHub a safe and wholesome environment for all Teknolohistas.',
        ]);
    }
}
