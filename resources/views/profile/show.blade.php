<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profile') }}
            </h2>
            <p class="text-sm text-gray-500">{{ __('Public identity and alumni details') }}</p>
        </div>
    </x-slot>

    <div class="pb-20">
        <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @php
                $isOwnProfile = $viewer->is($profileUser);
            @endphp

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="relative h-40 sm:h-52 lg:h-60">
                    <img src="{{ $profileUser->profileBannerUrl() }}"
                        alt="{{ __('Profile banner for :name', ['name' => $profileUser->name]) }}"
                        onerror="this.onerror=null;this.src='{{ asset('images/default-banner.svg') }}';"
                        class="h-full w-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent"></div>
                </div>

                <div class="relative px-6 pb-6 pt-20 sm:px-8 sm:pb-8 sm:pt-24">
                    <div
                        class="absolute -top-14 left-1/2 h-28 w-28 -translate-x-1/2 overflow-hidden rounded-full border-4 border-white bg-white shadow-lg sm:-top-16 sm:left-8 sm:h-32 sm:w-32 sm:translate-x-0">
                        <img src="{{ $profileUser->profileAvatarUrl() }}"
                            alt="{{ __('Profile photo for :name', ['name' => $profileUser->name]) }}"
                            onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';"
                            class="h-full w-full object-cover" />
                    </div>

                    <div class="flex flex-col gap-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="text-center sm:text-left">
                                <h3 class="text-2xl font-semibold text-gray-900 sm:text-3xl">{{ $profileUser->name }}</h3>
                                <p class="mt-1 text-sm text-gray-600 sm:text-base">
                                    {{ $profileUser->program_course ?? __('Program not yet provided') }}</p>
                                <div class="mt-3 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $profileUser->accountStatusBadgeClass() }}">
                                        {{ $profileUser->accountStatusLabel() }}
                                    </span>
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $profileUser->profileVisibilityBadgeClass() }}">
                                        {{ $profileUser->profileVisibilityLabel() }}
                                    </span>
                                </div>
                            </div>

                            @if ($isOwnProfile)
                                <a href="{{ route('profile.edit') }}"
                                    class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
                                    {{ __('Edit Profile Settings') }}
                                </a>
                            @endif
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Batch Year') }}</p>
                                <p class="mt-1 text-sm font-medium text-gray-900">{{ $profileUser->batch_year ?? __('Not provided') }}</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Connections') }}</p>
                                <p class="mt-1 text-sm font-medium text-gray-900">{{ __('Coming soon') }}</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Recent Posts') }}</p>
                                <p class="mt-1 text-sm font-medium text-gray-900">{{ __('Coming soon') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="!mt-3 grid gap-3 lg:grid-cols-3">
                <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">{{ __('About') }}</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-700">
                        {{ __('This profile section will include alumni bio, career highlights, and social activity once the posting and connection modules are integrated.') }}
                    </p>

                    @if ($showFullDetails)
                        <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                                {{ __('Full Contact Details') }}</p>
                            <p class="mt-1 text-sm font-medium text-emerald-900">
                                {{ __('Email: :email', ['email' => $profileUser->email]) }}</p>
                        </div>
                    @else
                        <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                            {{ __('Additional profile details are hidden until your account is verified.') }}
                        </div>
                    @endif
                </article>

                <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">{{ __('Activity') }}</h3>
                    <ul class="mt-3 space-y-3 text-sm text-gray-700">
                        <li class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                            {{ __('Recent interactions will appear here.') }}
                        </li>
                        <li class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                            {{ __('Saved content and milestones are coming in a future update.') }}
                        </li>
                    </ul>
                </article>
            </section>
        </div>
    </div>

    <x-footer />
</x-app-layout>