<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_low_id',
        'user_high_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function userLow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_low_id');
    }

    public function userHigh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_high_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    /**
     * Find (or create) the single conversation between two users.
     */
    public static function betweenUsers(User $a, User $b): self
    {
        [$lowId, $highId] = Connection::normalizedPair($a->id, $b->id);

        return static::firstOrCreate(
            ['user_low_id' => $lowId, 'user_high_id' => $highId],
        );
    }

    public function hasParticipant(User $user): bool
    {
        return $this->user_low_id === $user->id || $this->user_high_id === $user->id;
    }

    public function otherParticipantFor(User $user): ?User
    {
        if ($this->user_low_id === $user->id) {
            return $this->userHigh;
        }

        if ($this->user_high_id === $user->id) {
            return $this->userLow;
        }

        return null;
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_low_id', $user->id)
                ->orWhere('user_high_id', $user->id);
        });
    }
}
