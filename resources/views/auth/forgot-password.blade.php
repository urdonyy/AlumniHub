<x-guest-layout>
    <x-slot name="title">Forgot Password</x-slot>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <div x-data="{
        emailEmpty: {{ old('email') ? 'false' : 'true' }},
        showToast: {{ (session('status') || $errors->any()) ? 'true' : 'false' }},
        isSuccess: {{ session('status') ? 'true' : 'false' }},
        checkFields() {
            if (this.emailEmpty) {
                this.showToast = false;
            }
        }
    }">
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <input id="email" name="email" type="email"
                    value="{{ old('email') }}"
                    required autofocus autocomplete="username"
                    @input="emailEmpty = $event.target.value === ''; checkFields()"
                    :class="showToast
                        ? (isSuccess ? 'border-green-500 focus:ring-green-400' : 'border-red-500 focus:ring-red-400')
                        : 'border-gray-300 focus:ring-yellow-500'"
                    class="block mt-1 w-full rounded-md shadow-sm focus:border-none" />
            </div>

            <!-- Inline toast (error or success) -->
            @if ($errors->any() || session('status'))
                <div x-show="showToast"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    :class="isSuccess
                        ? 'border-green-300 bg-green-50 text-green-700'
                        : 'border-red-300 bg-red-50 text-red-700'"
                    class="mt-4 flex items-center gap-2.5 rounded-lg border px-4 py-3 text-sm font-semibold">

                    {{-- Success icon --}}
                    <svg x-show="isSuccess" class="h-4 w-4 shrink-0 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>

                    {{-- Error icon --}}
                    <svg x-show="!isSuccess" class="h-4 w-4 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z"
                            clip-rule="evenodd" />
                    </svg>

                    <span>{{ session('status') ?? $errors->first() }}</span>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <x-primary-button>
                    {{ __('Submit Email') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
