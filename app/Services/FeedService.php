<?php

namespace App\Services;

use App\Models\Community;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedService
{
    public function getUserFeed(User $user, int $perPage = 15, array $flairIds = []): LengthAwarePaginator
    {
        $flairIds = array_values(array_unique(array_map('intval', $flairIds)));

        // Unverified users browse read-only: public posts, plus members posts from
        // the communities they're auto-joined to (General Alumni Hub + program-batch).
        // They can't view connections posts. Matches PostPolicy::view + the community feed.
        if (! $user->isVerified()) {
            $query = Post::with(['user', 'community', 'flairs', 'media'])
                ->where('status', 'published')
                ->whereNull('trashed_at')
                ->where(function ($q) use ($user) {
                    $q->where('visibility', 'public')
                        ->orWhere(function ($q2) use ($user) {
                            $q2->where('visibility', 'members')
                                ->whereIn('community_id', $user->communities()->pluck('communities.id'));
                        });
                });

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
            ->whereNull('trashed_at')
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

    /**
     * Pulse-ranked, flair-filterable feed scoped to a single community.
     * Members (and admins) see every post; everyone else sees public posts only.
     */
    public function getCommunityFeed(Community $community, User $user, int $perPage = 10, array $flairIds = []): LengthAwarePaginator
    {
        $flairIds = array_values(array_unique(array_map('intval', $flairIds)));

        // Members (including unverified, auto-joined members) see every post in the
        // community; non-members see public posts only. Interaction stays gated by
        // verification, but viewing members posts is allowed for members.
        $canSeeAll = $user->communities()->whereKey($community->id)->exists()
            || $user->canManageCommunities();

        $query = Post::with(['user', 'community', 'flairs', 'media'])
            ->where('status', 'published')
            ->whereNull('trashed_at')
            ->where('community_id', $community->id)
            ->when(! $canSeeAll, fn ($q) => $q->where('visibility', 'public'));

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
