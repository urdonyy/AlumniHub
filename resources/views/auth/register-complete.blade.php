<x-auth-layout>
    <x-slot name="title">Registration Complete</x-slot>

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Complete your profile</h1>
    <p class="text-sm text-gray-500 mb-6">Step 3 of 3 — tell us about yourself and verify your identity.</p>

    {{-- Step indicator --}}
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Complete your profile</p>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1.5 opacity-50">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-green-600 text-xs font-bold text-white">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-gray-500">Account Created</span>
            </div>
            <div class="h-px flex-1 bg-green-300"></div>
            <div class="flex items-center gap-1.5 opacity-50">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-green-600 text-xs font-bold text-white">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-gray-500">Email Verified</span>
            </div>
            <div class="h-px flex-1 bg-gray-200"></div>
            <div class="flex items-center gap-1.5">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-700 text-xs font-bold text-white">3</span>
                <span class="text-xs font-semibold text-red-700">Complete Profile</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('register.complete.store') }}" enctype="multipart/form-data"
        x-data="documentUpload()" x-on:submit="onSubmit($event)">
        @csrf

        {{-- ── Personal Information ─────────────────────────────────────────────── --}}
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Personal Information</p>

        <div class="grid grid-cols-2 gap-3">
            <!-- First Name -->
            <div>
                <x-input-label for="first_name" :value="__('First Name')" />
                <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name"
                    :value="old('first_name')" required autofocus autocomplete="given-name" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
            </div>
            <!-- Last Name -->
            <div>
                <x-input-label for="last_name" :value="__('Last Name')" />
                <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name"
                    :value="old('last_name')" required autocomplete="family-name" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
            </div>
        </div>

        {{-- ── Student / Alumni Information ─────────────────────────────────────── --}}
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mt-5 mb-3">Student Information</p>

        <!-- Role -->
        <div>
            <x-input-label for="role" :value="__('I am a…')" />
            <select id="role" name="role"
                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                required>
                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select role</option>
                <option value="alumni" {{ old('role') === 'alumni' ? 'selected' : '' }}>Alumni (graduated)</option>
                <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student (currently enrolled)</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-1" />
        </div>

        <div class="grid grid-cols-2 gap-3 mt-3">
            <!-- Batch Year -->
            <div>
                <x-input-label for="batch_year" :value="__('Batch Year')" />
                <x-text-input id="batch_year" class="block mt-1 w-full" type="number" name="batch_year"
                    :value="old('batch_year')" min="2000" max="{{ now()->format('Y') }}" required
                    placeholder="{{ now()->format('Y') }}" />
                <x-input-error :messages="$errors->get('batch_year')" class="mt-1" />
            </div>
            <!-- Student Number -->
            <div>
                <x-input-label for="student_number" :value="__('Student No. (optional)')" />
                <x-text-input id="student_number" class="block mt-1 w-full" type="text" name="student_number"
                    :value="old('student_number')" autocomplete="off" placeholder="e.g. 2019-XXXXX-MN-0" />
                <x-input-error :messages="$errors->get('student_number')" class="mt-1" />
            </div>
        </div>

        <!-- Program / Course -->
        <div class="mt-3">
            <x-input-label for="program_course" :value="__('Program / Course')" />
            <select id="program_course" name="program_course"
                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                required>
                <option value="" disabled {{ old('program_course') ? '' : 'selected' }}>Select your program</option>
                @foreach ([
                    'Diploma in Civil Engineering Technology (DCvET)',
                    'Diploma in Computer Engineering Technology (DCET)',
                    'Diploma in Electrical Engineering Technology (DEET)',
                    'Diploma in Electronics Engineering Technology (DECET)',
                    'Diploma in Information Communication Technology (DICT)',
                    'Diploma in Mechanical Engineering Technology (DMET)',
                    'Diploma in Office Management Technology (DOMT)',
                    'Diploma in Railway Engineering Technology (DRET)',
                ] as $program)
                    <option value="{{ $program }}" {{ old('program_course') === $program ? 'selected' : '' }}>
                        {{ $program }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('program_course')" class="mt-1" />
        </div>

        {{-- ── Verification Document ────────────────────────────────────────────── --}}
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mt-5 mb-1">Alumni Verification</p>
        <p class="text-xs text-gray-500 mb-3">
            Upload a document that proves you are a PUP-ITECH alumnus or student —
            e.g. Transcript of Records (TOR), school ID, or diploma.
            PDF, JPG, PNG — max 5 MB.
        </p>

        <div>
            <x-input-label for="document" :value="__('Verification Document')" />
            <input id="document" name="document" type="file" accept=".pdf,.jpg,.jpeg,.png" required
                x-on:change="onPick($event)"
                class="block mt-1 w-full text-sm text-gray-700
                       file:mr-3 file:rounded-md file:border-0
                       file:bg-red-700 file:px-4 file:py-2
                       file:text-xs file:font-semibold file:uppercase file:tracking-widest file:text-white
                       hover:file:bg-red-800 cursor-pointer" />
            <p x-show="clientError" x-text="clientError" x-cloak
                class="mt-1 text-xs font-medium text-red-700"></p>
            <x-input-error :messages="$errors->get('document')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <button type="submit" form="discard-registration"
                class="text-sm text-gray-500 underline hover:text-gray-700 focus:outline-none">
                {{ __('Use a different email') }}
            </button>
            <x-primary-button>
                {{ __('Submit & Continue') }}
            </x-primary-button>
        </div>
    </form>

    <form id="discard-registration" method="POST" action="{{ route('register.discard') }}"
        onsubmit="return confirm('Discard this account and start over with a different email? Your current unfinished registration will be deleted.');">
        @csrf
    </form>

    <script>
        function documentUpload() {
            const MAX_BYTES = 5 * 1024 * 1024;
            const ALLOWED = ['application/pdf', 'image/jpeg', 'image/png'];
            return {
                clientError: '',
                onPick(event) {
                    this.clientError = '';
                    const file = event.target.files?.[0];
                    if (!file) return;
                    if (ALLOWED.length && file.type && !ALLOWED.includes(file.type)) {
                        this.clientError = 'Unsupported file type. Use PDF, JPG, or PNG.';
                        event.target.value = '';
                        return;
                    }
                    if (file.size > MAX_BYTES) {
                        const mb = (file.size / 1024 / 1024).toFixed(1);
                        this.clientError = `File is ${mb} MB. Please upload a file under 5 MB.`;
                        event.target.value = '';
                    }
                },
                onSubmit(event) {
                    if (this.clientError) {
                        event.preventDefault();
                    }
                },
            };
        }
    </script>

</x-auth-layout>
