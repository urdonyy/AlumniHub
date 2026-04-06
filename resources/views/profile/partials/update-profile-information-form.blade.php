<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
        <span class="font-medium text-gray-900">{{ __('Status') }}:</span>
        <span
            class="ml-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $user->accountStatusBadgeClass() }}">
            {{ $user->accountStatusLabel() }}
        </span>
        <p class="mt-2 text-gray-600">
            {{ $user->isVerifiedAlumni() ? __('Your alumni verification is approved.') : __('You can keep using the system while your verification is pending review.') }}
        </p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="first_name" :value="__('First Name')" />
                <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full"
                    :value="old('first_name', $user->first_name)" required autofocus autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            <div>
                <x-input-label for="last_name" :value="__('Last Name')" />
                <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full"
                    :value="old('last_name', $user->last_name)" required autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="batch_year" :value="__('Batch Year')" />
                <x-text-input id="batch_year" name="batch_year" type="number" class="mt-1 block w-full"
                    :value="old('batch_year', $user->batch_year)" min="2024" max="{{ now()->format('Y') }}" required />
                <x-input-error class="mt-2" :messages="$errors->get('batch_year')" />
            </div>

            <div>
                <x-input-label for="program_course" :value="__('Program / Course')" />
                <select id="program_course" name="program_course"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required>
                    <option value="" disabled {{ old('program_course', $user->program_course) ? '' : 'selected' }}>
                        Select program/course</option>
                    <option value="Diploma in Civil Engineering Technology (DCvET)" {{ old('program_course', $user->program_course) === 'Diploma in Civil Engineering Technology (DCvET)' ? 'selected' : '' }}>
                        Diploma in Civil Engineering Technology (DCvET)</option>
                    <option value="Diploma in Computer Engineering Technology (DCET)" {{ old('program_course', $user->program_course) === 'Diploma in Computer Engineering Technology (DCET)' ? 'selected' : '' }}>Diploma in Computer Engineering Technology (DCET)</option>
                    <option value="Diploma in Electrical Engineering Technology (DEET)" {{ old('program_course', $user->program_course) === 'Diploma in Electrical Engineering Technology (DEET)' ? 'selected' : '' }}>Diploma in Electrical Engineering Technology (DEET)</option>
                    <option value="Diploma in Electronics Engineering Technology (DECET)" {{ old('program_course', $user->program_course) === 'Diploma in Electronics Engineering Technology (DECET)' ? 'selected' : '' }}>Diploma in Electronics Engineering Technology (DECET)</option>
                    <option value="Diploma in Information Communication Technology (DICT)" {{ old('program_course', $user->program_course) === 'Diploma in Information Communication Technology (DICT)' ? 'selected' : '' }}>Diploma in Information Communication Technology (DICT)</option>
                    <option value="Diploma in Mechanical Engineering Technology (DMET)" {{ old('program_course', $user->program_course) === 'Diploma in Mechanical Engineering Technology (DMET)' ? 'selected' : '' }}>Diploma in Mechanical Engineering Technology (DMET)</option>
                    <option value="Diploma in Office Management Technology (DOMT)" {{ old('program_course', $user->program_course) === 'Diploma in Office Management Technology (DOMT)' ? 'selected' : '' }}>
                        Diploma in Office Management Technology (DOMT)</option>
                    <option value="Diploma in Railway Engineering Technology (DRET)" {{ old('program_course', $user->program_course) === 'Diploma in Railway Engineering Technology (DRET)' ? 'selected' : '' }}>Diploma in Railway Engineering Technology (DRET)</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('program_course')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>