<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Flair;

class Community extends Model
{
    use HasFactory;

    public const TYPE_PROGRAM_BATCH = 'program_batch';
    public const TYPE_TOPIC = 'topic';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'created_by',
        'is_system',
        'system_key',
        'type',
        'batch_year',
        'program_course',
        'year_section',
        'purpose',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'batch_year' => 'integer',
        ];
    }

    public function isProgramBatch(): bool
    {
        return $this->type === self::TYPE_PROGRAM_BATCH;
    }

    public function scopeProgramBatch($query)
    {
        return $query->where('type', self::TYPE_PROGRAM_BATCH);
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(CommunityJoinRequest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CommunityRule::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function moderators(): HasMany
    {
        return $this->hasMany(CommunityModerator::class);
    }

    /**
     * Check if a user is a moderator of this community.
     */
    public function isModerator(User $user): bool
    {
        return $this->moderators()->where('user_id', $user->id)->exists();
    }

    /**
     * Add a moderator to this community.
     */
    public function addModerator(User $user, string $role = 'moderator'): void
    {
        $this->moderators()->updateOrCreate(
            ['user_id' => $user->id],
            ['role' => $role, 'promoted_at' => now()]
        );
    }
    public function flairs(): HasMany
    {
        return $this->hasMany(Flair::class);
    }
}
