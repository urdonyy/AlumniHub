<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $filter = $request->query('filter', 'all');

        $query = $user->notifications()->whereNull('trashed_at');

        match ($filter) {
            'read'    => $query->whereNotNull('read_at'),
            'unread'  => $query->whereNull('read_at'),
            'starred' => $query->whereNotNull('starred_at'),
            default   => null,
        };

        $notifications = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $base         = $user->notifications()->whereNull('trashed_at');
        $filterCounts = [
            'all'     => (clone $base)->count(),
            'read'    => (clone $base)->whereNotNull('read_at')->count(),
            'unread'  => (clone $base)->whereNull('read_at')->count(),
            'starred' => (clone $base)->whereNotNull('starred_at')->count(),
        ];
        $unreadCount  = $filterCounts['unread'];
        $trashedCount = $user->notifications()->whereNotNull('trashed_at')->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'trashedCount', 'filter', 'filterCounts'));
    }

    public function trash(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->whereNotNull('trashed_at')
            ->orderByDesc('trashed_at')
            ->paginate(20);

        return view('notifications.trash', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $notification)
    {
        $dbNotification = $this->findOwned($request, $notification);

        $dbNotification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        $redirectTo = $request->input('redirect_to');
        if ($redirectTo && str_starts_with($redirectTo, '/')) {
            return redirect($redirectTo);
        }

        return redirect()->back();
    }

    public function markAsUnread(Request $request, string $notification)
    {
        $dbNotification = $this->findOwned($request, $notification);

        $dbNotification->markAsUnread();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->notifications()
            ->whereNull('trashed_at')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back();
    }

    public function toggleStar(Request $request, string $notification)
    {
        $dbNotification = $this->findOwned($request, $notification);

        $dbNotification->starred_at = $dbNotification->starred_at ? null : now();
        $dbNotification->save();

        if ($request->expectsJson()) {
            return response()->json(['starred' => (bool) $dbNotification->starred_at]);
        }

        return redirect()->back();
    }

    public function starMany(Request $request)
    {
        $ids     = (array) $request->input('ids', []);
        $starred = $request->boolean('starred', true);

        $request->user()
            ->notifications()
            ->whereNull('trashed_at')
            ->whereIn('id', $ids)
            ->update(['starred_at' => $starred ? now() : null]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'starred' => $starred]);
        }

        return redirect()->back();
    }

    public function destroy(Request $request, string $notification)
    {
        $dbNotification = $this->findOwned($request, $notification);

        $dbNotification->trashed_at = now();
        $dbNotification->save();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    public function restore(Request $request, string $notification)
    {
        $dbNotification = $this->findOwnedTrashed($request, $notification);

        $dbNotification->trashed_at = null;
        $dbNotification->save();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    public function forceDestroy(Request $request, string $notification)
    {
        $dbNotification = $this->findOwnedTrashed($request, $notification);

        $dbNotification->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    public function emptyTrash(Request $request)
    {
        $request->user()->notifications()->whereNotNull('trashed_at')->delete();

        return redirect()->route('notifications.trash');
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()
                ->notifications()
                ->whereNull('trashed_at')
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    private function findOwned(Request $request, string $id): DatabaseNotification
    {
        $n = $request->user()->notifications()->whereNull('trashed_at')->where('id', $id)->first();
        abort_unless($n, 404);
        return $n;
    }

    private function findOwnedTrashed(Request $request, string $id): DatabaseNotification
    {
        $n = $request->user()->notifications()->whereNotNull('trashed_at')->where('id', $id)->first();
        abort_unless($n, 404);
        return $n;
    }
}
