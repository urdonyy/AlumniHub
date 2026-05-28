<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationDocument;
use App\Services\CommunityAutoJoinService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'           => '',
            'email'          => $request->string('email')->value(),
            'password'       => Hash::make($request->string('password')->value()),
            'account_status' => 'pending',
        ]);

        event(new Registered($user));

        Auth::login($user);

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

    // ─── Step 3: Handle complete-registration submission ─────────────────────────

    public function storeComplete(Request $request, CommunityAutoJoinService $communityAutoJoinService): RedirectResponse
    {
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
}
