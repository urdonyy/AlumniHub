<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostEvent extends Model
{
    protected $table = 'post_events';

    protected $fillable = [
        'post_id',
        'event_type',
        'starts_at',
        'ends_at',
        'external_link',
        'address',
        'venue',
        'auto_invited',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_invited' => 'boolean',
    ];

    /**
     * Get the post this event belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Whether this is an online event (vs in-person).
     */
    public function isOnline(): bool
    {
        return $this->event_type === 'online';
    }
}
