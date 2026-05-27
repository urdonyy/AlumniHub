<x-app-layout>
    @php
        $sections = [
            'account-status' => [
                'label' => __('Account Status'),
                'description' => __('Review verification and visibility details.'),
            ],
            'profile-information' => [
                'label' => __('Profile Information'),
                'description' => __('Update personal details and career profile.'),
            ],
            'verification-document' => [
                'label' => __('Verification Document'),
                'description' => __('Upload your alumni verification document.'),
            ],
            'password' => [
                'label' => __('Password'),
                'description' => __('Change your account password.'),
            ],
            'delete-account' => [
                'label' => __('Delete Account'),
                'description' => __('Permanently remove your account.'),
            ],
        ];

        $activeSection = array_key_exists(request('section'), $sections)
            ? request('section')
            : 'account-status';
    @endphp

    <div class="bg-cover bg-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0"
        style="background-image: url('{{ asset('images/element.png') }}');">
        <x-slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                    {{ __('Edit Profile Settings') }}
                </h2>
                <p class="text-sm text-red-900">{{ __('Edit your profile information') }}</p>
            </div>
        </x-slot>

        <div class="pb-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-12">
                    <aside class="lg:col-span-3">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm lg:sticky lg:top-6">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Sections') }}</p>
                            <div class="mt-3 space-y-2">
                                @foreach ($sections as $sectionKey => $section)
                                    <a href="{{ route('profile.edit', ['section' => $sectionKey]) }}"
                                        class="block rounded-xl px-4 py-3 transition {{ $activeSection === $sectionKey ? 'bg-red-900 text-white shadow-sm' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                                        <span class="block text-sm font-semibold">{{ $section['label'] }}</span>
                                        <span class="mt-1 block text-xs {{ $activeSection === $sectionKey ? 'text-white/80' : 'text-gray-500' }}">
                                            {{ $section['description'] }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </aside>

                    <main class="lg:col-span-9">
                        @if ($activeSection === 'account-status')
                            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
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
                        @endif

                        @if ($activeSection === 'profile-information')
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-8">
                                <div class="max-w-3xl">
                                    @include('profile.partials.update-profile-information-form')
                                </div>
                            </div>
                        @endif

                        @if ($activeSection === 'verification-document')
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-8">
                                <div class="max-w-xl">
                                    @include('profile.partials.upload-verification-document-form')
                                </div>
                            </div>
                        @endif

                        @if ($activeSection === 'password')
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-8">
                                <div class="max-w-xl">
                                    @include('profile.partials.update-password-form')
                                </div>
                            </div>
                        @endif

                        @if ($activeSection === 'delete-account')
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-8">
                                <div class="max-w-xl">
                                    @include('profile.partials.delete-user-form')
                                </div>
                            </div>
                        @endif
                    </main>
                </div>
            </div>
        </div>
    </div>
    <x-footer />
</x-app-layout>