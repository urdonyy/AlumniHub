<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReport;
use App\Notifications\PostRemovedByReportNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostReportAdminController extends Controller
{
    /**
     * Review queue: posts that have crossed the report threshold and await an
     * admin decision (keep or delete), newest flag first.
     */
    public function index(): View
    {
        $flaggedPosts = Post::query()
            ->whereNotNull('flagged_at')
            ->whereNull('trashed_at')
            ->with([
                'user',
                'community',
                'media',
                'flairs',
                'event',
                'reports' => fn ($q) => $q->pending()->with('user')->latest(),
            ])
            ->orderByDesc('flagged_at')
            ->get();

        return view('admin.reports.index', [
            'flaggedPosts' => $flaggedPosts,
            'reasons' => PostReport::REASONS,
        ]);
    }

    /**
     * Keep the post: dismiss all pending reports and clear the flag. The post
     * stays live; future reports start the count again from zero.
     */
    public function keep(Post $post): RedirectResponse
    {
        $post->reports()->pending()->update([
            'resolved_at' => now(),
            'resolution' => 'kept',
        ]);

        $post->forceFill([
            'reports_count' => 0,
            'flagged_at' => null,
        ])->save();

        return back()->with('success', 'Post kept and reports dismissed.');
    }

    /**
     * Remove the post for a guideline violation and notify the author with the
     * reason + a suspension warning. Hard delete (the model's deleting hook
     * purges media and cascades the reports) so the author can't restore it.
     */
    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', Rule::in(array_keys(PostReport::REASONS))],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // Notify the author before the post (and its reports) are deleted.
        $post->user->notify(new PostRemovedByReportNotification(
            post: $post,
            reason: $validated['reason'],
            note: $validated['note'] ?? null,
        ));

        $post->delete();

        return back()->with('success', 'Post removed and the author has been notified.');
    }
}
