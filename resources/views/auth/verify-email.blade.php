<x-auth-layout>

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Check your inbox</h1>
    <p class="text-sm text-gray-500 mb-6">Step 2 of 3 — verify your email address.</p>

    {{-- Step indicator --}}
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Verify your email</p>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1.5 opacity-50">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-green-600 text-xs font-bold text-white">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-gray-500">Account Created</span>
            </div>
            <div class="h-px flex-1 bg-gray-200"></div>
            <div class="flex items-center gap-1.5">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-700 text-xs font-bold text-white">2</span>
                <span class="text-xs font-semibold text-red-700">Verify Email</span>
            </div>
            <div class="h-px flex-1 bg-gray-200"></div>
            <div class="flex items-center gap-1.5 opacity-40">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-300 text-xs font-bold text-white">3</span>
                <span class="text-xs font-semibold text-gray-500">Complete Profile</span>
            </div>
        </div>
    </div>

    {{-- Mail icon --}}
    <div class="flex justify-center mb-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 ring-1 ring-amber-200">
            <svg class="h-7 w-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
    </div>

    <p class="text-center text-sm text-gray-600 mb-4">
        We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
        Click the link in that email to continue.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 text-center">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="flex flex-col gap-3 mt-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full justify-center">
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full text-center text-sm text-gray-500 underline hover:text-gray-700 focus:outline-none">
                {{ __('Use a different account') }}
            </button>
        </form>
    </div>

</x-auth-layout>
