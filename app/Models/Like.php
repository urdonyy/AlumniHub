<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
    ];

    public $timestamps = false;

    /**
     * Get the post this like belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who liked this post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
