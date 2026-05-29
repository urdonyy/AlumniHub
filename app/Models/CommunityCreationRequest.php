<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityCreationRequest extends Model
{
    public const STATUS_PENDING_CO_MODS = 'pending_co_mods';
    public const STATUS_PENDING_ADMIN = 'pending_admin';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'requestor_id',
        'name',
        'description',
        'purpose',
        'batch_year',
        'program_course',
        'year_section',
        'status',
        'admin_id',
        'admin_note',
        'community_id',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'batch_year' => 'integer',
            'decided_at' => 'datetime',
        ];
    }

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function coModeratorInvites(): HasMany
    {
        return $this->hasMany(CommunityCreationRequestModerator::class, 'request_id');
    }

    public function scopePendingAdmin($query)
    {
        return $query->where('status', self::STATUS_PENDING_ADMIN);
    }

    public function allCoModsAccepted(): bool
    {
        $invites = $this->coModeratorInvites;

        if ($invites->isEmpty()) {
            return false;
        }

        return $invites->every(fn ($invite) => $invite->status === CommunityCreationRequestModerator::STATUS_ACCEPTED);
    }

    public function anyCoModDeclined(): bool
    {
        return $this->coModeratorInvites
            ->contains(fn ($invite) => $invite->status === CommunityCreationRequestModerator::STATUS_DECLINED);
    }
}
