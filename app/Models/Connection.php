<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Connection extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'user_low_id',
        'user_high_id',
        'status',
        'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function otherPartyFor(User $user): ?User
    {
        if ($this->sender_id === $user->id) {
            return $this->recipient;
        }

        if ($this->recipient_id === $user->id) {
            return $this->sender;
        }

        return null;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public static function normalizedPair(int $firstUserId, int $secondUserId): array
    {
        return [
            min($firstUserId, $secondUserId),
            max($firstUserId, $secondUserId),
        ];
    }
}
