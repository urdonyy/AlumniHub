<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Connection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'first_name', 'last_name', 'role', 'account_status', 'batch_year', 'program_course', 'skills', 'avatar_path', 'banner_path', 'avatar_uploaded_at', 'banner_uploaded_at', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    public function isVerifiedAlumni(): bool
    {
        return $this->role === 'alumni' && $this->account_status === 'approved';
    }

    public function isVerified(): bool
    {
        return $this->role === 'admin' || $this->account_status === 'approved';
    }

    public function canInteractInCommunities(): bool
    {
        return $this->role === 'admin' || $this->isVerifiedAlumni();
    }

    public function canManageCommunities(): bool
    {
        return $this->role === 'admin';
    }

    public function canCreatePosts(): bool
    {
        return $this->canInteractInCommunities();
    }

    public function canCommentOnPosts(): bool
    {
        return $this->canInteractInCommunities();
    }

    public function canSendMessages(): bool
    {
        return $this->canInteractInCommunities();
    }

    public function canSendConnectionRequests(): bool
    {
        return $this->isVerified();
    }

    public function hasLimitedProfileVisibility(): bool
    {
        return ! $this->isVerified();
    }

    public function profileVisibilityLabel(): string
    {
        return $this->hasLimitedProfileVisibility()
            ? 'Limited profile visibility'
            : 'Full profile visibility';
    }

    public function profileVisibilityBadgeClass(): string
    {
        return $this->hasLimitedProfileVisibility()
            ? 'bg-slate-100 text-slate-700 ring-slate-200'
            : 'bg-emerald-100 text-emerald-800 ring-emerald-200';
    }

    public function communityAccessLabel(): string
    {
        return $this->canInteractInCommunities()
            ? 'Full community access'
            : 'Read-only community access';
    }

    public function communityAccessBadgeClass(): string
    {
        return $this->canInteractInCommunities()
            ? 'bg-emerald-100 text-emerald-800 ring-emerald-200'
            : 'bg-slate-100 text-slate-700 ring-slate-200';
    }

    public function accountStatusLabel(): string
    {
        return match ($this->account_status) {
            'approved' => 'Verified',
            'rejected' => 'Unverified (Rejected)',
            default => $this->hasSubmittedVerificationDocument()
                ? 'Unverified (Pending Review)'
                : 'Unverified (Not Submitted)',
        };
    }

    public function hasSubmittedVerificationDocument(): bool
    {
        return $this->verificationDocuments()->exists();
    }

    public function hasPendingVerificationDocument(): bool
    {
        return $this->verificationDocuments()->where('status', 'pending')->exists();
    }

    public function accountStatusBadgeClass(): string
    {
        return match ($this->account_status) {
            'approved' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            'rejected' => 'bg-rose-100 text-rose-800 ring-rose-200',
            default => 'bg-amber-100 text-amber-800 ring-amber-200',
        };
    }

    public function verificationBadgeClass(): string
    {
        return match ($this->account_status) {
            'approved' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'rejected' => 'bg-rose-100 text-rose-700 ring-rose-200',
            default => 'bg-amber-100 text-amber-700 ring-amber-200',
        };
    }

    /**
     * @return array<int, string>
     */
    public function parsedSkills(): array
    {
        if (! $this->skills) {
            return [];
        }

        return collect(explode(',', $this->skills))
            ->map(fn(string $skill) => trim($skill))
            ->filter(fn(string $skill) => $skill !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function verificationDocuments()
    {
        return $this->hasMany(VerificationDocument::class);
    }

    public function profileEducations(): HasMany
    {
        return $this->hasMany(ProfileEducation::class)
            ->orderByDesc('start_date')
            ->orderByDesc('id');
    }

    public function reviewedVerificationDocuments()
    {
        return $this->hasMany(VerificationDocument::class, 'reviewed_by');
    }

    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class)->withTimestamps();
    }

    public function programBatchCommunity(): ?Community
    {
        return $this->communities()
            ->where('communities.type', Community::TYPE_PROGRAM_BATCH)
            ->first();
    }

    public function moderatedCommunities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class, 'community_moderators')
            ->withPivot('role', 'promoted_at')
            ->withTimestamps();
    }

    public function isModeratorOf(Community $community): bool
    {
        return $community->moderators()->where('user_id', $this->id)->exists();
    }

    public function sentConnections(): HasMany
    {
        return $this->hasMany(Connection::class, 'sender_id');
    }

    public function receivedConnections(): HasMany
    {
        return $this->hasMany(Connection::class, 'recipient_id');
    }

    public function pendingReceivedInvites(): HasMany
    {
        return $this->receivedConnections()->where('status', Connection::STATUS_PENDING);
    }

    public function pendingSentInvites(): HasMany
    {
        return $this->sentConnections()->where('status', Connection::STATUS_PENDING);
    }

    public function connections()
    {
        return Connection::query()
            ->where(function ($query) {
                $query->where('sender_id', $this->id)
                    ->orWhere('recipient_id', $this->id);
            })
            ->where('status', Connection::STATUS_ACCEPTED);
    }

    public function isConnectedWith(User $other): bool
    {
        [$lowId, $highId] = Connection::normalizedPair($this->id, $other->id);

        return Connection::query()
            ->where('user_low_id', $lowId)
            ->where('user_high_id', $highId)
            ->where('status', Connection::STATUS_ACCEPTED)
            ->exists();
    }

    public function profileExperiences(): HasMany
    {
        return $this->hasMany(ProfileExperience::class)
            ->orderByDesc('start_date')
            ->orderByDesc('id');
    }

    public function profileAvatarUrl(): string
    {
        if ($this->avatar_path) {
            return Storage::url($this->avatar_path);
        }

        return asset('images/default-avatar.svg');
    }

    public function profileBannerUrl(): string
    {
        if ($this->banner_path) {
            return Storage::url($this->banner_path);
        }

        return asset('images/default-banner.svg');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "batch_year" => "integer",
            'avatar_uploaded_at' => 'datetime',
            'banner_uploaded_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
