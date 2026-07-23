<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InstitutionSwitchController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // A fresh login must never inherit a stale "acting as institution" flag
        // from a previous session (cookie sessions persist in the browser, so a
        // DB wipe doesn't clear them).
        $request->session()->forget(InstitutionSwitchController::SESSION_KEY);

        // Discard a stashed "intended" URL pointing at a JSON-only endpoint
        // (e.g. /notifications/unread-count caught from a background poll).
        $intended = $request->session()->get('url.intended');
        if ($intended && str_contains((string) $intended, '/notifications/unread-count')) {
            $request->session()->forget('url.intended');
        }

        $redirect = redirect()->intended(route('dashboard', absolute: false));

        // Nudge still-unverified users to complete verification on each login.
        $user = $request->user();
        if ($user && ! $user->isVerified()) {
            $redirect->with('show_setup_prompt', true);
        }

        return $redirect;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
