<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Account Status') }}</p>
                        <div
                            class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $user->accountStatusBadgeClass() }}">
                            {{ $user->accountStatusLabel() }}
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Profile Visibility') }}</p>
                        <div
                            class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $user->profileVisibilityBadgeClass() }}">
                            {{ $user->profileVisibilityLabel() }}
                        </div>
                    </div>

                    <p class="text-sm text-gray-600">
                        {{ $user->isVerifiedAlumni() ? __('Your alumni verification is complete.') : __('You can still access your account while the verification request is being reviewed.') }}
                    </p>

                    @if ($user->hasLimitedProfileVisibility())
                        <div
                            class="grid gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 sm:grid-cols-2">
                            <div>
                                <span class="font-medium text-gray-900">{{ __('Visible') }}:</span>
                                <p class="mt-1">{{ __('Name, batch year, program/course, and verification status.') }}</p>
                            </div>

                            <div>
                                <span class="font-medium text-gray-900">{{ __('Hidden until verified') }}:</span>
                                <p class="mt-1">
                                    {{ __('Deeper profile details, contact information, and other sensitive fields.') }}</p>
                            </div>
                        </div>
                    @else
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                            {{ __('Your verified profile can be shown in full across the platform.') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.upload-verification-document-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>