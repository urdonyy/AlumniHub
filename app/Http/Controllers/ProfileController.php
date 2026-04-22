<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request, User $user): View
    {
        $viewer = $request->user();

        return view('profile.show', [
            'profileUser' => $user,
            'viewer' => $viewer,
            'showFullDetails' => ! $viewer->hasLimitedProfileVisibility(),
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
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

        $user->fill([
            'name' => trim($firstName . ' ' . $lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => Str::of($validated['email'])->trim()->value(),
            'batch_year' => (int) $validated['batch_year'],
            'program_course' => Str::of($validated['program_course'])->trim()->value(),
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

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
