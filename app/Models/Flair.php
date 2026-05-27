<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Flair extends Model
{
    protected $fillable = [
        'community_id',
        'name',
        'slug',
        'color',
        'icon',
        'is_sticky',
    ];

    protected $casts = [
        'is_sticky' => 'boolean',
    ];

    /**
     * Get the community this flair belongs to (or null for global).
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class)->withDefault();
    }

    /**
     * Get the posts with this flair.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'flair_post');
    }

    /**
     * Scope to global flairs.
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('community_id');
    }

    /**
     * Scope to community-specific flairs.
     */
    public function scopeForCommunity($query, $communityId)
    {
        return $query->where('community_id', $communityId)->orWhereNull('community_id');
    }
}
