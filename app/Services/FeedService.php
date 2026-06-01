<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedService
{
    public function getUserFeed(User $user, int $perPage = 15, array $flairIds = []): LengthAwarePaginator
    {
        $flairIds = array_values(array_unique(array_map('intval', $flairIds)));

        // Unverified users may only browse public posts.
        if (! $user->isVerified()) {
            $query = Post::with(['user', 'community', 'flairs', 'media'])
                ->where('status', 'published')
                ->where('visibility', 'public');

            return $this->withFlairRanking($query, $flairIds)->paginate($perPage);
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
            });

        return $this->withFlairRanking($query, $flairIds)->paginate($perPage);
    }

    private function withFlairRanking($query, array $flairIds)
    {
        $pulseScore = '(UNIX_TIMESTAMP(published_at) + (likes_count * 3600) + (comments_count * 7200))';

        if (empty($flairIds)) {
            return $query
                ->withCount(['allComments as comments_count', 'likes as likes_count'])
                ->orderByRaw("$pulseScore DESC");
        }

        // selectRaw must come before withCount so `posts.*` appears before the
        // count subqueries in the SELECT list — MariaDB rejects `*` mid-column-list.
        $placeholders = implode(',', array_fill(0, count($flairIds), '?'));

        return $query
            ->selectRaw(
                "posts.*, (SELECT COUNT(*) FROM flair_post WHERE flair_post.post_id = posts.id AND flair_post.flair_id IN ($placeholders)) as flair_match_count",
                $flairIds
            )
            ->withCount(['allComments as comments_count', 'likes as likes_count'])
            ->whereHas('flairs', fn ($q) => $q->whereIn('flairs.id', $flairIds))
            ->orderByDesc('flair_match_count')
            ->orderByRaw("$pulseScore DESC");
    }
}
