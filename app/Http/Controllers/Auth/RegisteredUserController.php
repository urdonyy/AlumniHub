<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $currentYear = (int) now()->format('Y');

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(['alumni', 'student'])],
            'batch_year' => ['required', 'integer', 'between:2024,' . $currentYear],
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $firstName = Str::of($request->string('first_name'))->trim()->value();
        $lastName = Str::of($request->string('last_name'))->trim()->value();

        $user = User::create([
            'name' => trim($firstName . ' ' . $lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $request->string('role')->value(),
            'account_status' => 'pending',
            'batch_year' => (int) $request->input('batch_year'),
            'program_course' => $request->string('program_course')->trim()->value(),
            'email' => $request->string('email')->value(),
            'password' => Hash::make($request->string('password')->value()),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
