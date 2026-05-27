<x-auth-layout>

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Create your account</h1>
    <p class="text-sm text-gray-500 mb-2">Step 1 of 3 — enter your email and a secure password.</p>

    {{-- Step indicator --}}
    <div class="flex items-center gap-2 mb-6">
        <div class="flex items-center gap-1.5">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-700 text-xs font-bold text-white">1</span>
            <span class="text-xs font-semibold text-red-700">Create Account</span>
        </div>
        <div class="h-px flex-1 bg-gray-200"></div>
        <div class="flex items-center gap-1.5 opacity-40">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-300 text-xs font-bold text-white">2</span>
            <span class="text-xs font-semibold text-gray-400">Verify Email</span>
        </div>
        <div class="h-px flex-1 bg-gray-200"></div>
        <div class="flex items-center gap-1.5 opacity-40">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-300 text-xs font-bold text-white">3</span>
            <span class="text-xs font-semibold text-gray-400">Complete Profile</span>
        </div>
    </div>

    <form method="POST" action="{{ route('register') }}" x-data="passwordStrength()">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password with eye toggle -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-1" x-data="{ show: false }">
                <input id="password" name="password"
                    x-bind:type="show ? 'text' : 'password'"
                    required autocomplete="new-password"
                    class="block w-full rounded-md border-gray-300 pr-10 shadow-sm focus:border-none focus:ring-yellow-500"
                    x-on:input="check($event.target.value)" />
                <button type="button" @click="show = !show" tabindex="-1"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />

            {{-- Live password requirements --}}
            <div class="mt-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs space-y-1" x-show="touched" x-cloak>
                <p class="font-semibold text-gray-500 mb-1">Password must contain:</p>
                <div class="flex items-center gap-1.5" :class="rules.length ? 'text-green-600' : 'text-gray-400'">
                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            :d="rules.length ? 'M5 13l4 4L19 7' : 'M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0'" />
                    </svg>
                    At least 8 characters
                </div>
                <div class="flex items-center gap-1.5" :class="rules.uppercase ? 'text-green-600' : 'text-gray-400'">
                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            :d="rules.uppercase ? 'M5 13l4 4L19 7' : 'M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0'" />
                    </svg>
                    One uppercase letter (A–Z)
                </div>
                <div class="flex items-center gap-1.5" :class="rules.lowercase ? 'text-green-600' : 'text-gray-400'">
                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            :d="rules.lowercase ? 'M5 13l4 4L19 7' : 'M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0'" />
                    </svg>
                    One lowercase letter (a–z)
                </div>
                <div class="flex items-center gap-1.5" :class="rules.number ? 'text-green-600' : 'text-gray-400'">
                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            :d="rules.number ? 'M5 13l4 4L19 7' : 'M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0'" />
                    </svg>
                    One number (0–9)
                </div>
                <div class="flex items-center gap-1.5" :class="rules.special ? 'text-green-600' : 'text-gray-400'">
                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            :d="rules.special ? 'M5 13l4 4L19 7' : 'M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0'" />
                    </svg>
                    One special character (!@#$%^&amp;*)
                </div>
            </div>
        </div>

        <!-- Confirm Password with eye toggle -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div class="relative mt-1" x-data="{ show: false }">
                <input id="password_confirmation" name="password_confirmation"
                    x-bind:type="show ? 'text' : 'password'"
                    required autocomplete="new-password"
                    class="block w-full rounded-md border-gray-300 pr-10 shadow-sm focus:border-none focus:ring-yellow-500" />
                <button type="button" @click="show = !show" tabindex="-1"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit -->
        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-2.5 text-sm">
                {{ __('Create Account') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Sign In link -->
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
            Already have an account?
            <a href="{{ route('login') }}"
                class="font-semibold text-red-800 hover:text-red-700 underline underline-offset-2">
                Sign in
            </a>
        </p>
    </div>

    <script>
        function passwordStrength() {
            return {
                touched: false,
                rules: { length: false, uppercase: false, lowercase: false, number: false, special: false },
                check(value) {
                    this.touched = value.length > 0;
                    this.rules.length    = value.length >= 8;
                    this.rules.uppercase = /[A-Z]/.test(value);
                    this.rules.lowercase = /[a-z]/.test(value);
                    this.rules.number    = /[0-9]/.test(value);
                    this.rules.special   = /[^A-Za-z0-9]/.test(value);
                }
            }
        }
    </script>

</x-auth-layout>
