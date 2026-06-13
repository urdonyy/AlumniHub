<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InstitutionSwitchController;
use App\Models\User;
use App\Models\VerificationDocument;
use App\Services\CommunityAutoJoinService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    // ─── Step 1: Show create-account form ───────────────────────────────────────

    public function create(): View
    {
        return view('auth.register');
    }

    // ─── Step 1: Handle create-account submission ────────────────────────────────

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                // No spaces anywhere in the email.
                'regex:/^\S+$/',
                // Only PUP-ITECH alumni/student addresses (Gmail or the school domain).
                'regex:/@(gmail\.com|iskolarngbayan\.pup\.edu\.ph)$/i',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $existing = User::where('email', $value)->first();

                    if ($existing && $existing->hasVerifiedEmail()) {
                        $fail(__('validation.unique', ['attribute' => $attribute]));
                    }
                },
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.regex' => 'Please use a real @gmail.com or @iskolarngbayan.pup.edu.ph email address (no spaces).',
        ]);

        $email = $request->string('email')->value();

        $user = DB::transaction(function () use ($request, $email) {
            User::where('email', $email)
                ->whereNull('email_verified_at')
                ->first()
                ?->delete();

            return User::create([
                'name'           => '',
                'email'          => $email,
                'password'       => Hash::make($request->string('password')->value()),
                'account_status' => 'pending',
            ]);
        });

        // Send the verification email, but never let an SMTP failure (e.g. a
        // provider daily-send cap) hard-block registration. The account is
        // already created; if mail fails the user still reaches the verification
        // notice page and can use "Resend" once the provider recovers.
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            report($e);
        }

        Auth::login($user);

        // Never let a newly registered account inherit a stale "acting as
        // institution" flag left in the browser's cookie session.
        $request->session()->forget(InstitutionSwitchController::SESSION_KEY);

        return redirect()->route('verification.notice');
    }

    // ─── Step 3: Show complete-registration form ─────────────────────────────────

    public function showComplete(Request $request): View|RedirectResponse
    {
        if (! empty($request->user()->first_name)) {
            return redirect()->route('dashboard');
        }

        return view('auth.register-complete');
    }

    // ─── Step 3: Discard incomplete account so the user can restart with a new email ─

    public function discard(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! empty($user->first_name) || $user->hasSubmittedVerificationDocument()) {
            return redirect()->route('dashboard');
        }

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('register')
            ->with('status', 'previous-registration-discarded');
    }

    // ─── Step 3: Handle complete-registration submission ─────────────────────────

    public function storeComplete(Request $request, CommunityAutoJoinService $communityAutoJoinService): RedirectResponse
    {
        if ($this->postBodyExceededLimit($request)) {
            throw ValidationException::withMessages([
                'document' => __('The verification document is too large. Please upload a file under 5 MB.'),
            ]);
        }

        $currentYear = (int) now()->format('Y');

        $validated = $request->validate([
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'role'           => ['required', Rule::in(['alumni', 'student'])],
            'batch_year'     => ['required', 'integer', 'between:2000,' . $currentYear],
            'program_course' => ['required', Rule::in([
                'Diploma in Civil Engineering Technology (DCvET)',
                'Diploma in Computer Engineering Technology (DCET)',
                'Diploma in Electrical Engineering Technology (DEET)',
                'Diploma in Electronics Engineering Technology (DECET)',
                'Diploma in Information Communication Technology (DICT)',
                'Diploma in Mechanical Engineering Technology (DMET)',
                'Diploma in Office Management Technology (DOMT)',
                'Diploma in Railway Engineering Technology (DRET)',
            ])],
            'student_number' => ['nullable', 'string', 'max:50'],
            'document'       => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user      = $request->user();
        $firstName = Str::of($validated['first_name'])->trim()->value();
        $lastName  = Str::of($validated['last_name'])->trim()->value();

        $user->update([
            'name'           => trim($firstName . ' ' . $lastName),
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'role'           => $validated['role'],
            'batch_year'     => (int) $validated['batch_year'],
            'program_course' => Str::of($validated['program_course'])->trim()->value(),
        ]);

        $documentPath = $request->file('document')->store(
            "verifications/user_{$user->id}"
        );

        VerificationDocument::create([
            'user_id'       => $user->id,
            'document_path' => $documentPath,
            'status'        => 'pending',
        ]);

        $communityAutoJoinService->attachMatchingCommunities($user);

        return redirect()->route('dashboard')
            ->with('status', 'registration-complete')
            ->with('show_setup_prompt', true);
    }

    /**
     * Detect a POST that was rejected because the body exceeded post_max_size.
     * In that case PHP silently drops $_POST/$_FILES while Content-Length still
     * reflects the original payload, so we surface a friendly validation error
     * instead of letting the request fall through as "document field required".
     */
    private function postBodyExceededLimit(Request $request): bool
    {
        if (! $request->isMethod('post')) {
            return false;
        }

        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        $postMax       = $this->iniBytes((string) ini_get('post_max_size'));

        if ($postMax <= 0 || $contentLength <= 0) {
            return false;
        }

        return $contentLength > $postMax
            && empty($_POST)
            && empty($_FILES);
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        $unit   = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g'     => $number * 1024 * 1024 * 1024,
            'm'     => $number * 1024 * 1024,
            'k'     => $number * 1024,
            default => $number,
        };
    }
}
