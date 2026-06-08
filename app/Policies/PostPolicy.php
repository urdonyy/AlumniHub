<?php

namespace App\Policies;

use App\Models\Community;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine if the user can view the post.
     */
    public function view(?User $user, Post $post): bool
    {
        // Public posts are viewable by anyone
        if ($post->visibility === 'public') {
            return true;
        }

        // Authors can always view their own posts regardless of visibility
        if ($user && $user->id === $post->user_id) {
            return true;
        }

        if (!$user) {
            return false;
        }

        // Members-only posts are viewable by any member of the community — including
        // unverified members (everyone is auto-joined to General Alumni Hub and their
        // program-batch). It's read-only for them; interaction is gated by verification.
        if ($post->visibility === 'members') {
            return $this->isCommunityMember($user, $post->community);
        }

        // Connections posts require a verified, connected account.
        if (!$user->isVerified()) {
            return false;
        }

        if ($post->visibility === 'connections') {
            return $user->isConnectedWith($post->user) || $this->isCommunityMember($user, $post->community);
        }

        return false;
    }

    /**
     * Determine if the user can create a post in the community.
     */
    public function create(User $user, Community $community): bool
    {
        // Only community members can create posts
        return $this->isCommunityMember($user, $community);
    }

    /**
     * Determine if the user can update the post (edit via composer).
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine if the user can trash (soft-delete) the post.
     * Authors trash their own; moderators/admins can also trash community posts.
     */
    public function trash(User $user, Post $post): bool
    {
        if ($user->id === $post->user_id) {
            return true;
        }

        // The institution account moderates only the system communities it belongs
        // to (General Alumni Hub + program communities). In batch communities it is
        // a regular non-member: view/like/comment on public posts, but no moderation.
        if ($user->isInstitution() && $post->community?->is_system) {
            return true;
        }

        return $this->isModeratorOrAdmin($user, $post->community);
    }

    /**
     * Determine if the user can delete the post (hard delete via mod action).
     */
    public function delete(User $user, Post $post): bool
    {
        if ($user->id === $post->user_id) {
            return true;
        }

        if ($user->isInstitution() && $post->community?->is_system) {
            return true;
        }

        return $this->isModeratorOrAdmin($user, $post->community);
    }

    /**
     * Determine if the user can restore a trashed post.
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine if the user can permanently delete a trashed post.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine if the user can assign flairs to the post.
     */
    public function assignFlair(User $user, Post $post): bool
    {
        // Only moderators and admins can assign flairs
        return $this->isModeratorOrAdmin($user, $post->community);
    }

    /**
     * Determine if the user can pin the post.
     */
    public function pin(User $user, Post $post): bool
    {
        // Only moderators and admins can pin posts
        return $this->isModeratorOrAdmin($user, $post->community);
    }

    /**
     * Check if user is a community member.
     */
    private function isCommunityMember(User $user, ?Community $community = null): bool
    {
        // Connections-only posts have no community.
        if (!$community) {
            return false;
        }

        return $community->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user is a moderator or admin of the community.
     */
    private function isModeratorOrAdmin(User $user, ?Community $community = null): bool
    {
        // Connections-only posts have no community.
        if (!$community) {
            return false;
        }

        // Check if user is a community moderator
        if ($community->isModerator($user)) {
            return true;
        }

        // Check if user is a system admin (has admin role or is super admin)
        return $user->is_admin ?? false;
    }
}
