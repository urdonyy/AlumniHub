<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'first_name', 'last_name', 'role', 'account_status', 'batch_year', 'program_course', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isVerifiedAlumni(): bool
    {
        return $this->role === 'alumni' && $this->account_status === 'approved';
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
        return $this->canInteractInCommunities();
    }

    public function hasLimitedProfileVisibility(): bool
    {
        return $this->role !== 'admin' && ! $this->isVerifiedAlumni();
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
            default => 'Unverified (Pending Review)',
        };
    }

    public function accountStatusBadgeClass(): string
    {
        return match ($this->account_status) {
            'approved' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            'rejected' => 'bg-rose-100 text-rose-800 ring-rose-200',
            default => 'bg-amber-100 text-amber-800 ring-amber-200',
        };
    }

    public function verificationDocuments()
    {
        return $this->hasMany(VerificationDocument::class);
    }

    public function reviewedVerificationDocuments()
    {
        return $this->hasMany(VerificationDocument::class, 'reviewed_by');
    }

    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class)->withTimestamps();
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
