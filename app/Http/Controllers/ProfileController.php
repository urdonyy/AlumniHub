<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Connection;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request, User $user): View
    {
        $viewer = $request->user();
        $user->load('profileExperiences');

        $connectionState = null;
        $activeConnection = null;

        if (! $viewer->is($user)) {
            [$lowId, $highId] = Connection::normalizedPair($viewer->id, $user->id);

            $activeConnection = Connection::query()
                ->where('user_low_id', $lowId)
                ->where('user_high_id', $highId)
                ->first();

            if ($activeConnection) {
                $connectionState = match ($activeConnection->status) {
                    Connection::STATUS_ACCEPTED => 'connected',
                    Connection::STATUS_PENDING => $activeConnection->sender_id === $viewer->id
                        ? 'invite_sent'
                        : 'invite_received',
                    default => null,
                };
            }
        }

        return view('profile.show', [
            'profileUser' => $user,
            'viewer' => $viewer,
            'showFullDetails' => ! $user->hasLimitedProfileVisibility(),
            'connectionState' => $connectionState,
            'activeConnection' => $activeConnection,
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $request->user()->load('profileExperiences');

        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $firstName = Str::of($validated['first_name'])->trim()->value();
        $lastName = Str::of($validated['last_name'])->trim()->value();
        $skills = collect(explode(',', (string) ($validated['skills'] ?? '')))
            ->map(fn(string $skill) => trim($skill))
            ->filter(fn(string $skill) => $skill !== '')
            ->unique()
            ->values();

        $user->fill([
            'name' => trim($firstName . ' ' . $lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => Str::of($validated['email'])->trim()->value(),
            'batch_year' => (int) $validated['batch_year'],
            'program_course' => Str::of($validated['program_course'])->trim()->value(),
            'skills' => $skills->isEmpty() ? null : $skills->implode(', '),
        ]);

        if ($request->hasFile('avatar')) {
            $oldAvatarPath = $user->avatar_path;
            $newAvatarPath = $request->file('avatar')->store('avatars/user_' . $user->id, 'public');

            $user->avatar_path = $newAvatarPath;
            $user->avatar_uploaded_at = now();

            if ($oldAvatarPath && Storage::disk('public')->exists($oldAvatarPath)) {
                Storage::disk('public')->delete($oldAvatarPath);
            }
        }

        if ($request->hasFile('banner')) {
            $oldBannerPath = $user->banner_path;
            $newBannerPath = $request->file('banner')->store('banners/user_' . $user->id, 'public');

            $user->banner_path = $newBannerPath;
            $user->banner_uploaded_at = now();

            if ($oldBannerPath && Storage::disk('public')->exists($oldBannerPath)) {
                Storage::disk('public')->delete($oldBannerPath);
            }
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        DB::transaction(function () use ($user, $validated): void {
            $user->save();

            $submittedExperiences = collect($validated['experiences'] ?? [])
                ->map(function (array $experience): array {
                    return [
                        'id' => isset($experience['id']) ? (int) $experience['id'] : null,
                        'title' => Str::of((string) ($experience['title'] ?? ''))->trim()->value(),
                        'organization' => Str::of((string) ($experience['organization'] ?? ''))->trim()->value(),
                        'start_date' => $this->normalizeExperienceMonth($experience['start_month'] ?? null),
                        'end_date' => $this->normalizeExperienceMonth($experience['end_month'] ?? null),
                        'description' => Str::of((string) ($experience['description'] ?? ''))->trim()->value(),
                    ];
                })
                ->filter(function (array $experience): bool {
                    return $experience['title'] !== ''
                        || $experience['organization'] !== ''
                        || $experience['start_date'] !== null
                        || $experience['end_date'] !== null
                        || $experience['description'] !== '';
                })
                ->values();

            $userExperienceIds = $user->profileExperiences()->pluck('id')->all();
            $keptExperienceIds = [];

            foreach ($submittedExperiences as $experience) {
                if ($experience['title'] === '' || $experience['organization'] === '') {
                    continue;
                }

                $payload = [
                    'title' => $experience['title'],
                    'organization' => $experience['organization'],
                    'start_date' => $experience['start_date'],
                    'end_date' => $experience['end_date'],
                    'description' => $experience['description'] === '' ? null : $experience['description'],
                ];

                if ($experience['id'] !== null && in_array($experience['id'], $userExperienceIds, true)) {
                    $user->profileExperiences()->whereKey($experience['id'])->update($payload);
                    $keptExperienceIds[] = $experience['id'];
                    continue;
                }

                $createdExperience = $user->profileExperiences()->create($payload);
                $keptExperienceIds[] = $createdExperience->id;
            }

            if (empty($keptExperienceIds)) {
                $user->profileExperiences()->delete();
                return;
            }

            $user->profileExperiences()->whereNotIn('id', $keptExperienceIds)->delete();
        });

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    private function normalizeExperienceMonth(?string $month): ?string
    {
        if (! $month) {
            return null;
        }

        $monthValue = trim($month);

        if ($monthValue === '') {
            return null;
        }

        return $monthValue . '-01';
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        if ($user->banner_path && Storage::disk('public')->exists($user->banner_path)) {
            Storage::disk('public')->delete($user->banner_path);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
