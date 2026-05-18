<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityModerator extends Model
{
    protected $table = 'community_moderators';

    protected $fillable = [
        'community_id',
        'user_id',
        'role',
        'promoted_at',
    ];

    protected $casts = [
        'promoted_at' => 'datetime',
    ];

    /**
     * Get the community.
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Get the user (moderator).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
