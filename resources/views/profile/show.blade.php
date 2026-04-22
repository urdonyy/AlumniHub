<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profile') }}
            </h2>
            <p class="text-sm text-gray-500">{{ __('Public identity and alumni details') }}</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @php
                $isOwnProfile = $viewer->is($profileUser);
                $canSeeCareerDetails = $isOwnProfile || $showFullDetails;
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
                        class="absolute -top-14 left-1/2 h-28 w-28 -translate-x-1/2 overflow-hidden rounded-full border-4 border-white bg-white shadow-lg sm:-top-16 sm:h-32 sm:w-32">
                        <img src="{{ $profileUser->profileAvatarUrl() }}"
                            alt="{{ __('Profile photo for :name', ['name' => $profileUser->name]) }}"
                            onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';"
                            class="h-full w-full object-cover" />
                    </div>

                    <div class="flex flex-col gap-5">
                        <div class="flex flex-col items-center gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="text-center">
                                <h3 class="text-2xl font-semibold text-gray-900 sm:text-3xl">{{ $profileUser->name }}</h3>
                                <p class="mt-1 text-sm text-gray-600 sm:text-base">
                                    {{ $profileUser->program_course ?? __('Program not yet provided') }}</p>
                                <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                                    <span
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-full ring-1 ring-inset {{ $profileUser->verificationBadgeClass() }}"
                                        title="{{ $profileUser->accountStatusLabel() }}" aria-label="{{ $profileUser->accountStatusLabel() }}">
                                        @if ($profileUser->account_status === 'approved')
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd"
                                                    d="M16.704 5.29a1 1 0 010 1.414l-7.07 7.07a1 1 0 01-1.415 0L3.296 8.85a1 1 0 011.415-1.414l4.216 4.216 6.363-6.363a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @elseif ($profileUser->account_status === 'rejected')
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3a1 1 0 00.293.707l2 2a1 1 0 101.414-1.414L11 9.586V7z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                        <span class="sr-only">{{ $profileUser->accountStatusLabel() }}</span>
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

            <section class="grid gap-6 lg:grid-cols-3">
                <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">{{ __('About') }}</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-700">
                        {{ __('This profile section will include alumni bio, career highlights, and social activity once the posting and connection modules are integrated.') }}
                    </p>

                    <div class="mt-6 space-y-4">
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-600">{{ __('Experience') }}</h4>
                            @if ($canSeeCareerDetails)
                                @if ($profileUser->profileExperiences->isEmpty())
                                    <p class="mt-2 text-sm text-gray-600">{{ __('No experience added yet.') }}</p>
                                @else
                                    <ul class="mt-2 space-y-3">
                                        @foreach ($profileUser->profileExperiences as $experience)
                                            <li class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                                <p class="text-sm font-semibold text-gray-900">{{ $experience->title }}</p>
                                                <p class="text-sm text-gray-700">{{ $experience->organization }}</p>
                                                <p class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-500">
                                                    {{ $experience->start_date?->format('M Y') ?? __('Start TBD') }}
                                                    -
                                                    {{ $experience->end_date?->format('M Y') ?? __('Present') }}
                                                </p>
                                                @if ($experience->description)
                                                    <p class="mt-2 text-sm text-gray-700">{{ $experience->description }}</p>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @else
                                <p class="mt-2 text-sm text-slate-700">
                                    {{ __('Experience is visible after verification.') }}
                                </p>
                            @endif
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-600">{{ __('Skills') }}</h4>
                            @if ($canSeeCareerDetails)
                                @if (empty($profileUser->parsedSkills()))
                                    <p class="mt-2 text-sm text-gray-600">{{ __('No skills added yet.') }}</p>
                                @else
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($profileUser->parsedSkills() as $skill)
                                            <span
                                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-200">
                                                {{ $skill }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <p class="mt-2 text-sm text-slate-700">
                                    {{ __('Skills are visible after verification.') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if ($showFullDetails)
                        <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                                {{ __('Full Contact Details') }}</p>
                            <p class="mt-1 text-sm font-medium text-emerald-900">
                                {{ __('Email: :email', ['email' => $profileUser->email]) }}</p>
                        </div>
                    @else
                        <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                            {{ __('Additional profile details are hidden until this account is verified.') }}
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
</x-app-layout>