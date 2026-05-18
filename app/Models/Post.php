<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'community_id',
        'user_id',
        'title',
        'body_markdown',
        'body_html',
        'status',
        'visibility',
        'pinned',
        'published_at',
    ];

    protected $casts = [
        'pinned' => 'boolean',
        'published_at' => 'datetime',
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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-convert markdown to HTML on save
        static::saving(function ($post) {
            if ($post->isDirty('body_markdown')) {
                $post->body_html = self::markdownToHtml($post->body_markdown);
            }

            // Auto-set published_at if transitioning to published
            if ($post->isDirty('status') && $post->status === 'published' && is_null($post->published_at)) {
                $post->published_at = now();
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
        return $query->where('status', 'published');
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
}
