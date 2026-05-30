<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedService
{
    public function getUserFeed(User $user, int $perPage = 15): LengthAwarePaginator
    {
        // Unverified users may only browse public posts.
        if (! $user->isVerified()) {
            return Post::with(['user', 'community', 'flairs', 'media'])
                ->withCount(['allComments as comments_count', 'likes as likes_count'])
                ->where('status', 'published')
                ->where('visibility', 'public')
                ->orderByRaw('(UNIX_TIMESTAMP(published_at) + (likes_count * 3600) + (comments_count * 7200)) DESC')
                ->paginate($perPage);
        }

        $connectedUserIds = $user->connections()
            ->get()
            ->map(function ($connection) use ($user) {
                return $connection->sender_id === $user->id
                    ? $connection->recipient_id
                    : $connection->sender_id;
            })
            ->unique()
            ->values();

        $query = Post::with(['user', 'community', 'flairs', 'media'])
            ->withCount(['allComments as comments_count', 'likes as likes_count'])
            ->where('status', 'published')
            ->where(function ($q) use ($user, $connectedUserIds) {
                $q->where('visibility', 'public')
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('visibility', 'members')
                            ->whereIn('community_id', $user->communities()->pluck('communities.id'));
                    })
                    ->orWhere(function ($q3) use ($user, $connectedUserIds) {
                        $q3->where('visibility', 'connections')
                            ->where(function ($q4) use ($user, $connectedUserIds) {
                                $q4->whereIn('user_id', $connectedUserIds)
                                    ->orWhere('user_id', $user->id);
                            });
                    });
            })
            ->orderByRaw('(UNIX_TIMESTAMP(published_at) + (likes_count * 3600) + (comments_count * 7200)) DESC');

        return $query->paginate($perPage);
    }
}
