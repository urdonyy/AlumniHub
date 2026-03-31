<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- First Name -->
        <div>
            <x-input-label for="first_name" :value="__('First Name')" />
            <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name"
                :value="old('first_name')" required autofocus autocomplete="given-name" />
            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
        </div>

        <!-- Last Name -->
        <div class="mt-4">
            <x-input-label for="last_name" :value="__('Last Name')" />
            <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name"
                :value="old('last_name')" required autocomplete="family-name" />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Role')" />
            <select id="role" name="role"
                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select role</option>
                <option value="alumni" {{ old('role') === 'alumni' ? 'selected' : '' }}>Alumni</option>
                <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Batch Year -->
        <div class="mt-4">
            <x-input-label for="batch_year" :value="__('Batch Year')" />
            <x-text-input id="batch_year" class="block mt-1 w-full" type="number" name="batch_year"
                :value="old('batch_year')" min="2024" max="{{ now()->format('Y') }}" required />
            <x-input-error :messages="$errors->get('batch_year')" class="mt-2" />
        </div>

        <!-- Program / Course -->
        <div class="mt-4">
            <x-input-label for="program_course" :value="__('Program / Course')" />
            <select id="program_course" name="program_course"
                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
                <option value="" disabled {{ old('program_course') ? '' : 'selected' }}>Select program/course</option>
                <option value="Diploma in Civil Engineering Technology (DCvET)" {{ old('program_course') === 'Diploma in Civil Engineering Technology (DCvET)' ? 'selected' : '' }}>Diploma in Civil Engineering Technology
                    (DCvET)</option>
                <option value="Diploma in Computer Engineering Technology (DCET)" {{ old('program_course') === 'Diploma in Computer Engineering Technology (DCET)' ? 'selected' : '' }}>Diploma in Computer Engineering
                    Technology (DCET)</option>
                <option value="Diploma in Electrical Engineering Technology (DEET)" {{ old('program_course') === 'Diploma in Electrical Engineering Technology (DEET)' ? 'selected' : '' }}>Diploma in Electrical Engineering
                    Technology (DEET)</option>
                <option value="Diploma in Electronics Engineering Technology (DECET)" {{ old('program_course') === 'Diploma in Electronics Engineering Technology (DECET)' ? 'selected' : '' }}>Diploma in Electronics Engineering Technology (DECET)</option>
                <option value="Diploma in Information Communication Technology (DICT)" {{ old('program_course') === 'Diploma in Information Communication Technology (DICT)' ? 'selected' : '' }}>Diploma in Information Communication Technology (DICT)</option>
                <option value="Diploma in Mechanical Engineering Technology (DMET)" {{ old('program_course') === 'Diploma in Mechanical Engineering Technology (DMET)' ? 'selected' : '' }}>Diploma in Mechanical Engineering
                    Technology (DMET)</option>
                <option value="Diploma in Office Management Technology (DOMT)" {{ old('program_course') === 'Diploma in Office Management Technology (DOMT)' ? 'selected' : '' }}>Diploma in Office Management Technology
                    (DOMT)</option>
                <option value="Diploma in Railway Engineering Technology (DRET)" {{ old('program_course') === 'Diploma in Railway Engineering Technology (DRET)' ? 'selected' : '' }}>Diploma in Railway Engineering
                    Technology (DRET)</option>
            </select>
            <x-input-error :messages="$errors->get('program_course')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>