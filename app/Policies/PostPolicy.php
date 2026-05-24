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

        // Members-only posts: check if user is a community member
        if ($post->visibility === 'members') {
            if (!$user) {
                return false;
            }

            return $this->isCommunityMember($user, $post->community);
        }

        // Connections visibility: check if user is connected with the post author or a community member
        if ($post->visibility === 'connections') {
            if (!$user) {
                return false;
            }

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
     * Determine if the user can update the post.
     */
    public function update(User $user, Post $post): bool
    {
        // Post author can update their own post
        if ($user->id === $post->user_id) {
            return true;
        }

        // Community moderators or admins can update any post in their community
        return $this->isModeratorOrAdmin($user, $post->community);
    }

    /**
     * Determine if the user can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        // Post author can delete their own post
        if ($user->id === $post->user_id) {
            return true;
        }

        // Community moderators or admins can delete any post in their community
        return $this->isModeratorOrAdmin($user, $post->community);
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
    private function isCommunityMember(User $user, Community $community): bool
    {
        return $community->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user is a moderator or admin of the community.
     */
    private function isModeratorOrAdmin(User $user, Community $community): bool
    {
        // Check if user is a community moderator
        if ($community->isModerator($user)) {
            return true;
        }

        // Check if user is a system admin (has admin role or is super admin)
        return $user->is_admin ?? false;
    }
}
