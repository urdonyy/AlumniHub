<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Community;
use App\Models\Post;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine if the user can view the comment.
     */
    public function view(?User $user, Comment $comment): bool
    {
        // Anyone who can view the post can view its comments
        return true;
    }

    /**
     * Determine if the user can create a comment on the post.
     */
    public function create(User $user, Post $post): bool
    {
        // Users must be verified alumni to comment
        if (!$user->isVerifiedAlumni()) {
            return false;
        }

        // Only community members can comment on posts
        return $this->isCommunityMember($user, $post->community);
    }

    /**
     * Determine if the user can update the comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Comment author can update their own comment (future feature)
        if ($user->id === $comment->user_id) {
            return true;
        }

        // Post author or community moderators can update any comment
        $post = $comment->post;
        return $user->id === $post->user_id || $this->isModeratorOrAdmin($user, $post->community);
    }

    /**
     * Determine if the user can delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Comment author can delete their own comment
        if ($user->id === $comment->user_id) {
            return true;
        }

        // Post author can delete any comment on their post
        $post = $comment->post;
        if ($user->id === $post->user_id) {
            return true;
        }

        // Community moderators or admins can delete any comment in their community
        return $this->isModeratorOrAdmin($user, $post->community);
    }

    /**
     * Check if user is a community member.
     */
    private function isCommunityMember(User $user, ?Community $community = null): bool
    {
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
        if (!$community) {
            return false;
        }

        // Check if user is a community moderator
        if ($community->isModerator($user)) {
            return true;
        }

        // Check if user is a system admin
        return $user->is_admin ?? false;
    }
}
