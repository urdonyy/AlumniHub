<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'community_id',
        'user_id',
        'title',
        'body_markdown',
        'body_html',
        'status',
        'post_type',
        'visibility',
        'pinned',
        'published_at',
        'trashed_at',
        'reports_count',
        'flagged_at',
        'removed_by_user_id',
        'removal_reason',
        'removal_note',
    ];

    protected $casts = [
        'pinned' => 'boolean',
        'published_at' => 'datetime',
        'trashed_at' => 'datetime',
        'flagged_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the community this post belongs to.
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Get the user (author) of this post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the flairs attached to this post.
     */
    public function flairs(): BelongsToMany
    {
        return $this->belongsToMany(Flair::class, 'flair_post');
    }

    /**
     * Get the media (images) attached to this post.
     */
    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('order');
    }

    /**
     * Get the event details for this post (only present when post_type = 'event').
     */
    public function event(): HasOne
    {
        return $this->hasOne(PostEvent::class);
    }

    /**
     * Get the comments on this post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_comment_id')->with('user', 'replies');
    }

    /**
     * Get every comment on this post, including nested replies.
     * Used for an accurate comment count (the threaded `comments()`
     * relationship only counts top-level comments).
     */
    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the likes on this post.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get the abuse reports filed against this post.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(PostReport::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-convert markdown to HTML on save
        static::saving(function ($post) {
            if ($post->isDirty('body_markdown')) {
                $post->body_html = filled($post->body_markdown)
                    ? self::markdownToHtml($post->body_markdown)
                    : null;
            }

            // Auto-set published_at if transitioning to published
            if ($post->isDirty('status') && $post->status === 'published' && is_null($post->published_at)) {
                $post->published_at = now();
            }
        });

        // On PERMANENT deletion, remove the post's media files from storage so
        // they don't orphan and waste the storage quota. This only fires on a
        // real delete() (force-delete / hard-delete) — trashing uses an update
        // to `trashed_at`, so a trashed post keeps its files until purged.
        static::deleting(function ($post) {
            foreach ($post->media as $media) {
                if ($media->file_path) {
                    Storage::delete($media->file_path);
                }
                $media->delete();
            }
        });
    }

    /**
     * Convert Markdown to sanitized HTML.
     */
    public static function markdownToHtml(string $markdown): string
    {
        try {
            $converter = new \League\CommonMark\GithubFlavoredMarkdownConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
                // Honor single line breaks the author typed (render as <br>),
                // so a one-Enter line break shows up instead of being collapsed.
                'renderer' => [
                    'soft_break' => "<br>\n",
                ],
            ]);
            return $converter->convert($markdown)->getContent();
        } catch (\Throwable $e) {
            // Fallback: escape and wrap in p tag if conversion fails
            return '<p>' . htmlspecialchars($markdown) . '</p>';
        }
    }

    /**
     * Scope to published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNull('trashed_at');
    }

    public function scopeTrashed($query)
    {
        return $query->whereNotNull('trashed_at');
    }

    public function isTrashed(): bool
    {
        return $this->trashed_at !== null;
    }

    /**
     * Scope to community posts.
     */
    public function scopeInCommunity($query, $communityId)
    {
        return $query->where('community_id', $communityId);
    }

    /**
     * Scope to posts by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if the authenticated user has liked this post.
     */
    public function isLikedByAuthUser(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
