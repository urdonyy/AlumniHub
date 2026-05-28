<x-auth-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Welcome back</h1>
    <p class="text-sm text-gray-500 mb-6">Sign in to your AlumniHub account</p>

    <div x-data="{
        emailEmpty: {{ old('email') ? 'false' : 'true' }},
        passwordEmpty: true,
        showToast: {{ $errors->any() ? 'true' : 'false' }},
        showPassword: false,
        checkFields() {
            if (this.emailEmpty && this.passwordEmpty) {
                this.showToast = false;
            }
        }
    }">
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div>
                <x-input-label for="email" :value="__('Email Address')" />
                <input id="email" name="email" type="email"
                    value="{{ old('email') }}"
                    required autofocus autocomplete="username"
                    @input="emailEmpty = $event.target.value === ''; checkFields()"
                    :class="showToast ? 'border-red-500 focus:ring-red-400' : 'border-gray-300 focus:ring-yellow-500'"
                    class="block mt-1 w-full rounded-md shadow-sm focus:border-none" />
            </div>

            <!-- Password with eye toggle -->
            <div class="mt-4">
                <div class="flex items-center justify-between">
                    <x-input-label for="password" :value="__('Password')" />
                    @if (Route::has('password.request'))
                        <a class="text-xs text-gray-500 hover:text-red-800 underline"
                            href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>
                <div class="relative mt-1">
                    <input id="password" name="password"
                        :type="showPassword ? 'text' : 'password'"
                        required autocomplete="current-password"
                        @input="passwordEmpty = $event.target.value === ''; checkFields()"
                        :class="showToast ? 'border-red-500 focus:ring-red-400' : 'border-gray-300 focus:ring-yellow-500'"
                        class="block w-full rounded-md pr-10 shadow-sm focus:border-none" />
                    <button type="button" @click="showPassword = !showPassword" tabindex="-1"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                        {{-- Eye open --}}
                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{-- Eye closed --}}
                        <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Inline error toast -->
            @if ($errors->any())
                <div x-show="showToast"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="mt-4 flex items-center gap-2.5 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                    <svg class="h-4 w-4 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Remember Me -->
            <div class="mt-4">
                <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                    <input id="remember_me" type="checkbox"
                        class="rounded border-gray-300 text-red-900 shadow-sm focus:ring-yellow-500" name="remember">
                    <span class="text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <!-- Submit -->
            <div class="mt-6">
                <x-primary-button class="w-full justify-center py-2.5 text-sm">
                    {{ __('Sign In') }}
                </x-primary-button>
            </div>
        </form>
    </div>

    <!-- Sign Up link -->
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
            Don't have an account?
            <a href="{{ route('register') }}"
                class="font-semibold text-red-800 hover:text-red-700 underline underline-offset-2">
                Create an account
            </a>
        </p>
    </div>

</x-auth-layout>
