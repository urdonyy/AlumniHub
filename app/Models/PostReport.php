<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostReport extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'reason',
        'details',
        'resolved_at',
        'resolution',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Number of distinct pending reports at which a post enters the admin
     * review queue.
     */
    public const THRESHOLD = 5;

    /**
     * Report grounds: machine key => human label. Shared by the report modal
     * (reporter picks one) and the admin removal form (admin picks the reason
     * that goes into the author's notification).
     */
    public const REASONS = [
        'racism' => 'Racism or hate speech',
        'nsfw' => 'Nudity or pornography',
        'harassment' => 'Harassment or bullying',
        'fraud' => 'Fraud or scam',
        'spam' => 'Spam or misleading',
        'violence' => 'Violence or threats',
        'other' => 'Other',
    ];

    public static function reasonLabel(?string $key): string
    {
        return self::REASONS[$key] ?? 'Other';
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->whereNull('resolved_at');
    }
}
