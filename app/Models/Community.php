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

    protected $fillable = [
        'name',
        'slug',
        'description',
        'created_by',
        'is_system',
        'system_key',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
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

    public function moderators(): \Illuminate\Database\Eloquent\Relations\HasMany
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
