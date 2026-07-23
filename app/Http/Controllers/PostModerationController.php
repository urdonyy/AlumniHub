<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReport;
use App\Notifications\PostRemovedByReportNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostModerationController extends Controller
{
    use AuthorizesRequests;

    /**
     * A moderator/superadmin removes someone else's post with a reason.
     *
     * This is a soft removal (sets trashed_at) but, unlike an author trashing
     * their own post, it records who removed it + the reason — so the author
     * CANNOT restore it (see PostPolicy::restore) and is notified why.
     */
    public function remove(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        // Authors trash their own posts via the normal trash route; this endpoint
        // is strictly for a moderator acting on someone else's post.
        abort_if($post->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', Rule::in(array_keys(PostReport::REASONS))],
            'note'   => ['nullable', 'string', 'max:500'],
        ]);

        $author = $post->user;

        $post->update([
            'trashed_at'         => now(),
            'removed_by_user_id' => $request->user()->id,
            'removal_reason'     => $validated['reason'],
            'removal_note'       => $validated['note'] ?? null,
        ]);

        if ($author) {
            $author->notify(new PostRemovedByReportNotification(
                post: $post,
                reason: $validated['reason'],
                note: $validated['note'] ?? null,
            ));
        }

        return back()->with('success', 'Post removed and the author has been notified.');
    }
}
