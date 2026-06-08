<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Lets an admin "act as" the PUP-ITECH Official institution account and back.
 *
 * enter(): admin-gated. Stashes the admin's id in the session and logs in as
 * the institution, so the rest of the app sees a verified client (role
 * superadmin) with the institution's connections, communities and posts.
 *
 * exit(): available only inside an active switch (session holds impersonator_id);
 * restores the original admin.
 */
class InstitutionSwitchController extends Controller
{
    public const SESSION_KEY = 'institution_switch_admin_id';

    public function enter(Request $request): RedirectResponse
    {
        // Route is admin-gated, but guard against double-switching.
        if ($request->session()->has(self::SESSION_KEY)) {
            return redirect()->route('dashboard');
        }

        $institution = User::institution();

        if (! $institution) {
            return redirect()->route('admin.inbox')
                ->with('error', 'The institution account has not been set up yet.');
        }

        $adminId = Auth::id();
        Auth::login($institution);
        $request->session()->put(self::SESSION_KEY, $adminId);

        return redirect()->route('dashboard');
    }

    public function exit(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get(self::SESSION_KEY);

        // Only honor the switch-back if we're genuinely acting as the institution
        // right now. A stale flag carried into another account's session must
        // never log that account in as the stored admin (privilege escalation).
        if (! $adminId || ! $request->user()?->isInstitution()) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('dashboard');
        }

        $admin = User::find($adminId);
        $request->session()->forget(self::SESSION_KEY);

        if ($admin && $admin->role === 'admin') {
            Auth::login($admin);

            return redirect()->route('admin.inbox');
        }

        return redirect()->route('dashboard');
    }
}
