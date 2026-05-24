<x-app-layout>
    <div class="bg-cover bg-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0"
        style="background-image: url('{{ asset('images/element.png') }}');">
        <x-slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                    {{ __('Profile') }}
                </h2>
                <p class="text-sm text-red-900">{{ __('Public identity and alumni details') }}</p>
            </div>
        </x-slot>

        <div class="pb-24">
            <div class="max-w-5xl mx-auto space-y-6 sm:px-6 lg:px-8">
                @php
                    $isOwnProfile = $viewer->is($profileUser);
                    $canSeeCareerDetails = $isOwnProfile || $showFullDetails;
                @endphp

                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="relative h-40 sm:h-52 lg:h-60">
                        @if ($isOwnProfile)
                            <button type="button" data-profile-media-open
                                class="group relative block h-full w-full text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-900">
                                <img src="{{ $profileUser->profileBannerUrl() }}"
                                    alt="{{ __('Profile banner for :name', ['name' => $profileUser->name]) }}"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-banner.svg') }}';"
                                    class="h-full w-full object-cover transition duration-150 group-hover:scale-[1.01]" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent transition group-hover:from-black/55"></div>
                                <div class="absolute right-4 top-4 inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-red-900 shadow-sm backdrop-blur">
                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    <span>{{ __('Change Banner') }}</span>
                                </div>
                            </button>
                        @else
                            <img src="{{ $profileUser->profileBannerUrl() }}"
                                alt="{{ __('Profile banner for :name', ['name' => $profileUser->name]) }}"
                                onerror="this.onerror=null;this.src='{{ asset('images/default-banner.svg') }}';"
                                class="h-full w-full object-cover" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent"></div>
                        @endif
                    </div>

                    <div class="relative px-6 pb-6 pt-20 sm:px-8 sm:pb-8 sm:pt-24">
                        @if ($isOwnProfile)
                            <button type="button" data-profile-media-open
                                class="absolute -top-14 left-1/2 h-28 w-28 -translate-x-1/2 overflow-hidden rounded-full border-4 border-white bg-white shadow-lg transition hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2 sm:-top-16 sm:h-32 sm:w-32 lg:left-8 lg:translate-x-0">
                                <img src="{{ $profileUser->profileAvatarUrl() }}"
                                    alt="{{ __('Profile photo for :name', ['name' => $profileUser->name]) }}"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';"
                                    class="h-full w-full object-cover" />
                                <span class="absolute inset-x-0 bottom-0 flex items-center justify-center bg-black/50 py-1 text-[10px] font-semibold uppercase tracking-wide text-white">
                                    {{ __('Edit Photo') }}
                                </span>
                            </button>
                        @else
                            <div
                                class="absolute -top-14 left-1/2 h-28 w-28 -translate-x-1/2 overflow-hidden rounded-full border-4 border-white bg-white shadow-lg sm:-top-16 sm:h-32 sm:w-32 lg:left-8 lg:translate-x-0">
                                <img src="{{ $profileUser->profileAvatarUrl() }}"
                                    alt="{{ __('Profile photo for :name', ['name' => $profileUser->name]) }}"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';"
                                    class="h-full w-full object-cover" />
                            </div>
                        @endif

                        <div class="flex flex-col gap-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="text-center sm:text-left">
                                    <h3 class="text-2xl font-semibold text-gray-900 sm:text-3xl">
                                        {{ $profileUser->name }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600 sm:text-base">
                                        {{ $profileUser->program_course ?? __('Program not yet provided') }}
                                    </p>
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
                                        class="inline-flex self-end items-center justify-center px-3 py-1 bg-red-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase whitespace-nowrap tracking-widest hover:bg-white hover:text-red-900 hover:border-red-900 focus:bg-white focus:text-red-900 focus:border-red-900 active:bg-white focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2 transition ease-in-out duration-150 max-[1024px]:h-8 max-[1024px]:w-8 max-[1024px]:rounded-full max-[1024px]:px-0 max-[1024px]:py-0 lg:self-auto">
                                        <i class="fa-solid fa-user-pen text-sm min-[1025px]:hidden" aria-hidden="true"></i>
                                        <span class="sr-only">{{ __('Edit Profile Settings') }}</span>
                                        <span class="hidden min-[1025px]:inline">{{ __('Edit Profile Settings') }}</span>
                                    </a>
                                @elseif ($viewer->canSendConnectionRequests())
                                    @if ($connectionState === 'connected')
                                        <span
                                            class="inline-flex items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 px-3 py-1 sm:px-5 sm:py-1.5 text-xs font-semibold uppercase tracking-widest text-emerald-700">
                                            {{ __('Connected') }}
                                        </span>
                                    @elseif ($connectionState === 'invite_sent')
                                        <span
                                            class="inline-flex items-center justify-center rounded-md border border-amber-200 bg-amber-50 px-3 py-1 sm:px-5 sm:py-1.5 text-xs font-semibold uppercase tracking-widest text-amber-700">
                                            {{ __('Invited') }}
                                        </span>
                                    @elseif ($connectionState === 'invite_received' && $activeConnection)
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('connections.accept', $activeConnection) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-3 py-1 sm:px-4 sm:py-1.5 text-xs font-semibold uppercase tracking-widest text-white hover:bg-emerald-500">
                                                    {{ __('Accept') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('connections.ignore', $activeConnection) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-1 sm:px-4 sm:py-1.5 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                                                    {{ __('Ignore') }}
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('connections.invite', $profileUser) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center justify-center rounded-md bg-red-900 px-3 py-1 sm:px-5 sm:py-1.5 text-xs font-semibold uppercase tracking-widest text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2">
                                                {{ __('Connect') }}
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>

                            <div
                                class="grid gap-1 sm:gap-3 grid-cols-2 sm:grid-cols-3 sm:border sm:border-gray-200 sm:bg-gray-50 sm:rounded-lg">
                                <div
                                    class="p-0 sm:p-4 flex flex-col sm:flex-col gap-[6px] sm:gap-0 items-center justify-center relative min-w-0">
                                    <div class="flex items-center justify-center gap-1">
                                        <i
                                            class="fa-solid fa-graduation-cap text-red-900 text-xs sm:text-sm lg:text-xl"></i>
                                        <p class="text-sm sm:text-lg lg:text-2xl font-semibold text-red-900">
                                            {{ $profileUser->batch_year ?? __('Not provided') }}
                                        </p>
                                    </div>
                                    <p
                                        class="min-w-0 truncate text-xs lg:text-base font-semibold uppercase tracking-wide text-[#FFC107]">
                                        {{ __('Batch Year') }}
                                    </p>
                                    <div
                                        class="border border-red-900 absolute h-[50%] right-0 hidden sm:flex flex-col items-center">
                                    </div>
                                </div>
                                <div
                                    class="p-0 sm:p-4 flex flex-col sm:flex-col gap-[6px] sm:gap-0 items-center justify-center relative min-w-0 border-l border-red-900/30 sm:border-l-0">
                                    <div class="flex items-center justify-center gap-1">
                                        <i
                                            class="fa-solid fa-circle-nodes text-red-900 text-xs sm:text-sm lg:text-xl"></i>
                                        <p class="text-sm sm:text-lg lg:text-2xl font-semibold text-red-900">
                                            {{ __('67') }}
                                        </p>
                                    </div>
                                    <p
                                        class="min-w-0 truncate text-xs lg:text-base font-semibold uppercase tracking-wide text-[#FFC107]">
                                        {{ __('Connections') }}
                                    </p>
                                    <div
                                        class="border border-red-900 absolute h-[50%] right-0 hidden sm:flex flex-col items-center">
                                    </div>
                                </div>
                                <div
                                    class="col-span-2 sm:col-span-1 p-0 pt-2 sm:p-4 flex flex-col sm:flex-col gap-[6px] sm:gap-0 items-center justify-center min-w-0 border-t border-red-900/30 sm:border-t-0">
                                    <div class="flex items-center justify-center gap-1">
                                        <i
                                            class="fa-solid fa-pen-to-square text-red-900 text-xs sm:text-sm lg:text-xl"></i>
                                        <p class="text-sm sm:text-lg lg:text-2xl font-semibold text-red-900">
                                            {{ __('21') }}
                                        </p>
                                    </div>
                                    <p
                                        class="min-w-0 text-center sm:text-left truncate text-xs lg:text-base font-semibold uppercase tracking-wide text-[#FFC107]">
                                        {{ __('Recent Posts') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="!mt-3 grid gap-3 lg:grid-cols-3">
                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
                        <h3 class="text-base md:text-lg font-semibold tracking-wide text-gray-700">{{ __('About') }}
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-gray-600">
                            {{ __('This profile section will include alumni bio, career highlights, and social activity once the posting and connection modules are integrated.') }}
                        </p>

                        @if ($showFullDetails)
                            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                                    {{ __('Full Contact Details') }}
                                </p>
                                <p class="mt-1 text-sm font-medium text-emerald-900">
                                    {{ __('Email: :email', ['email' => $profileUser->email]) }}
                                </p>
                            </div>
                        @else
                            <div class="mt-5 rounded-lg border border-[#FFC107] bg-[#FFC107] p-4 text-sm text-red-900">
                                {{ __('Additional profile details are hidden until your account is verified.') }}
                            </div>
                        @endif
                    </article>

                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-base md:text-lg font-semibold tracking-wide text-gray-700">{{ __('Activity') }}
                        </h3>
                        <ul class="mt-3 space-y-3 text-sm text-gray-700">
                            <li class="rounded-lg border border-[#FFC107] bg-[#FFC107] px-3 py-2 text-red-900">
                                {{ __('Recent interactions will appear here.') }}
                            </li>
                            <li class="rounded-lg border border-[#FFC107] bg-[#FFC107] px-3 py-2 text-red-900">
                                {{ __('Saved content and milestones are coming in a future update.') }}
                            </li>
                        </ul>
                    </article>
                </section>

                @if ($isOwnProfile)
                    <div id="profile-media-modal"
                        class="fixed inset-0 z-50 hidden" role="dialog"
                        aria-modal="true" aria-labelledby="profile-media-modal-title" aria-hidden="true">
                        <button type="button" class="fixed inset-0 bg-black/60" data-profile-media-close
                            aria-label="{{ __('Close modal backdrop') }}"></button>

                        <div class="relative z-10 flex h-full w-full items-center justify-center px-4 py-6">
                            <div class="flex w-full max-w-3xl min-h-0 max-h-[calc(100vh-3rem)] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
                                <div class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-200 bg-white px-6 py-4">
                                    <div>
                                        <h3 id="profile-media-modal-title" class="text-lg font-semibold text-gray-900">{{ __('Edit Profile Media') }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">
                                            {{ __('Update your avatar and banner.') }}
                                        </p>
                                    </div>

                                    <button type="button" data-profile-media-close
                                        class="rounded-md p-1 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                                        aria-label="{{ __('Close modal') }}">
                                        <span aria-hidden="true" class="text-3xl sm:text-4xl leading-none">&times;</span>
                                    </button>
                                </div>

                                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data"
                                    class="flex-1 min-h-0 space-y-6 overflow-y-auto px-6 pb-6">
                                @csrf
                                @method('patch')

                                <input type="hidden" name="first_name" value="{{ old('first_name', $profileUser->first_name) }}" />
                                <input type="hidden" name="last_name" value="{{ old('last_name', $profileUser->last_name) }}" />
                                <input type="hidden" name="batch_year" value="{{ old('batch_year', $profileUser->batch_year) }}" />
                                <input type="hidden" name="program_course" value="{{ old('program_course', $profileUser->program_course) }}" />
                                <input type="hidden" name="email" value="{{ old('email', $profileUser->email) }}" />
                                <input type="hidden" name="skills" value="{{ old('skills', $profileUser->skills) }}" />

                                @foreach ($profileUser->profileExperiences as $experienceIndex => $experience)
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][id]" value="{{ $experience->id }}" />
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][title]" value="{{ $experience->title }}" />
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][organization]" value="{{ $experience->organization }}" />
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][start_month]" value="{{ $experience->start_date?->format('Y-m') }}" />
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][end_month]" value="{{ $experience->end_date?->format('Y-m') }}" />
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][description]" value="{{ $experience->description }}" />
                                @endforeach

                                    <div class="mt-4 space-y-6">
                                        <div>
                                            <x-input-label for="banner" :value="__('Banner Photo')" />
                                            <img id="banner-preview" src="{{ $profileUser->profileBannerUrl() }}" alt="{{ __('Current banner preview') }}"
                                                onerror="this.onerror=null;this.src='{{ asset('images/default-banner.svg') }}';"
                                                class="mt-2 h-32 w-full rounded-lg border border-gray-200 object-cover sm:h-40" />
                                            <input id="banner" name="banner" type="file" accept="image/jpeg,image/png,image/webp"
                                                class="mt-3 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-red-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-red-700 file:cursor-pointer" />
                                            <p class="mt-1 text-xs text-gray-500">{{ __('Accepted: JPG, PNG, WEBP. Max 5MB.') }}</p>
                                            <x-input-error class="mt-2" :messages="$errors->get('banner')" />
                                        </div>

                                        <div>
                                            <x-input-label for="avatar" :value="__('Profile Photo (Avatar)')" />
                                            <img id="avatar-preview" src="{{ $profileUser->profileAvatarUrl() }}" alt="{{ __('Current avatar preview') }}"
                                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';"
                                                class="mt-2 h-28 w-28 rounded-full border border-gray-200 object-cover" />
                                            <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                                                class="mt-3 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-red-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-red-700 file:cursor-pointer" />
                                            <p class="mt-1 text-xs text-gray-500">{{ __('Accepted: JPG, PNG, WEBP. Max 3MB.') }}</p>
                                            <p id="avatar-crop-message" class="mt-2 text-xs text-emerald-700"></p>
                                            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                                        </div>
                                    </div>

                                <div id="avatar-crop-modal"
                                    class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/60 px-4 py-6" role="dialog"
                                    aria-modal="true" aria-labelledby="avatar-crop-title">
                                    <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-2xl">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h4 id="avatar-crop-title" class="text-base font-semibold text-gray-900">{{ __('Adjust profile photo') }}</h4>
                                                <p class="mt-1 text-xs text-gray-600">{{ __('Drag the zoom and apply when ready.') }}</p>
                                            </div>
                                            <button id="close-avatar-crop-modal" type="button"
                                                class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                                                aria-label="{{ __('Close crop dialog') }}">&times;</button>
                                        </div>

                                        <div class="mt-4 flex items-center justify-center">
                                            <canvas id="avatar-crop-canvas" width="320" height="320"
                                                class="h-56 w-56 rounded-full border border-gray-200 bg-gray-100"></canvas>
                                        </div>

                                        <div class="mt-4">
                                            <label for="avatar-crop-zoom" class="text-xs font-medium text-gray-700">{{ __('Zoom') }}</label>
                                            <input id="avatar-crop-zoom" type="range" min="100" max="250" value="100"
                                                class="mt-1 w-full accent-red-900" />
                                        </div>

                                        <div class="mt-5 flex items-center justify-end gap-3">
                                            <button id="cancel-avatar-crop" type="button"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-700 transition hover:bg-gray-100">
                                                {{ __('Cancel') }}
                                            </button>
                                            <button id="apply-avatar-crop" type="button"
                                                class="inline-flex items-center rounded-md border border-gray-900 bg-gray-900 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-white hover:text-gray-900">
                                                {{ __('Apply Crop') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                    <div class="flex shrink-0 items-center justify-end gap-4 border-t border-gray-200 pt-4">
                                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const profileMediaModal = document.getElementById('profile-media-modal');
            const openProfileMediaButtons = document.querySelectorAll('[data-profile-media-open]');
            const closeProfileMediaButtons = document.querySelectorAll('[data-profile-media-close]');
            const bannerInput = document.getElementById('banner');
            const bannerPreview = document.getElementById('banner-preview');
            const avatarInput = document.getElementById('avatar');
            const avatarPreview = document.getElementById('avatar-preview');
            const cropModal = document.getElementById('avatar-crop-modal');
            const closeCropModalBtn = document.getElementById('close-avatar-crop-modal');
            const cancelCropBtn = document.getElementById('cancel-avatar-crop');
            const cropZoom = document.getElementById('avatar-crop-zoom');
            const cropCanvas = document.getElementById('avatar-crop-canvas');
            const applyCropBtn = document.getElementById('apply-avatar-crop');
            const cropMessage = document.getElementById('avatar-crop-message');

            if (!profileMediaModal || !bannerInput || !bannerPreview || !avatarInput || !avatarPreview || !cropModal || !closeCropModalBtn || !cancelCropBtn || !cropZoom || !cropCanvas || !applyCropBtn || !cropMessage) {
                return;
            }

            const context = cropCanvas.getContext('2d');
            let sourceImage = null;
            let sourceUrl = null;

            const syncBodyScroll = () => {
                const profileOpen = !profileMediaModal.classList.contains('hidden');
                const cropOpen = !cropModal.classList.contains('hidden');
                document.body.classList.toggle('overflow-hidden', profileOpen || cropOpen);
            };

            const openProfileMediaModal = () => {
                profileMediaModal.classList.remove('hidden');
                profileMediaModal.classList.add('flex');
                profileMediaModal.setAttribute('aria-hidden', 'false');
                syncBodyScroll();
            };

            const closeProfileMediaModal = () => {
                profileMediaModal.classList.add('hidden');
                profileMediaModal.classList.remove('flex');
                profileMediaModal.setAttribute('aria-hidden', 'true');
                if (!cropModal.classList.contains('hidden')) {
                    closeCropModal();
                    return;
                }
                syncBodyScroll();
            };

            const openCropModal = () => {
                cropModal.classList.remove('hidden');
                cropModal.classList.add('flex');
                syncBodyScroll();
            };

            const closeCropModal = () => {
                cropModal.classList.add('hidden');
                cropModal.classList.remove('flex');
                syncBodyScroll();
            };

            openProfileMediaButtons.forEach((button) => {
                button.addEventListener('click', openProfileMediaModal);
            });

            closeProfileMediaButtons.forEach((button) => {
                button.addEventListener('click', closeProfileMediaModal);
            });

            profileMediaModal.addEventListener('click', (event) => {
                if (event.target === profileMediaModal) {
                    closeProfileMediaModal();
                }
            });

            if (!profileMediaModal.classList.contains('hidden')) {
                syncBodyScroll();
            }

            @if ($errors->isNotEmpty())
                openProfileMediaModal();
            @endif

            const renderCrop = () => {
                if (!sourceImage || !context) {
                    return;
                }

                const zoom = Number(cropZoom.value) / 100;
                const base = Math.min(sourceImage.naturalWidth, sourceImage.naturalHeight);
                const sampleSize = Math.max(1, Math.floor(base / zoom));
                const sx = Math.floor((sourceImage.naturalWidth - sampleSize) / 2);
                const sy = Math.floor((sourceImage.naturalHeight - sampleSize) / 2);

                context.clearRect(0, 0, cropCanvas.width, cropCanvas.height);
                context.drawImage(sourceImage, sx, sy, sampleSize, sampleSize, 0, 0, cropCanvas.width, cropCanvas.height);
            };

            bannerInput.addEventListener('change', () => {
                const file = bannerInput.files && bannerInput.files[0];

                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    if (event.target && typeof event.target.result === 'string') {
                        bannerPreview.src = event.target.result;
                    }
                };
                reader.readAsDataURL(file);
            });

            avatarInput.addEventListener('change', () => {
                const file = avatarInput.files && avatarInput.files[0];
                cropMessage.textContent = '';

                if (!file) {
                    return;
                }

                if (sourceUrl) {
                    URL.revokeObjectURL(sourceUrl);
                }

                sourceUrl = URL.createObjectURL(file);
                cropZoom.value = '100';

                const reader = new FileReader();
                reader.onload = (event) => {
                    if (event.target && typeof event.target.result === 'string') {
                        avatarPreview.src = event.target.result;
                    }
                };
                reader.readAsDataURL(file);

                sourceImage = new Image();
                sourceImage.onload = () => {
                    renderCrop();
                    openCropModal();
                };
                sourceImage.src = sourceUrl;
            });

            cropZoom.addEventListener('input', renderCrop);

            cropModal.addEventListener('click', (event) => {
                if (event.target === cropModal) {
                    closeCropModal();
                }
            });

            closeCropModalBtn.addEventListener('click', closeCropModal);
            cancelCropBtn.addEventListener('click', closeCropModal);

            applyCropBtn.addEventListener('click', () => {
                if (!context || !sourceImage) {
                    return;
                }

                renderCrop();

                cropCanvas.toBlob((blob) => {
                    if (!blob) {
                        return;
                    }

                    const croppedFile = new File([blob], 'avatar-cropped.png', {
                        type: 'image/png'
                    });

                    const transfer = new DataTransfer();
                    transfer.items.add(croppedFile);
                    avatarInput.files = transfer.files;

                    avatarPreview.src = cropCanvas.toDataURL('image/png');
                    cropMessage.textContent = 'Crop applied. Save to upload this avatar.';
                    closeCropModal();
                }, 'image/png', 0.92);
            });
        });
    </script>

    <x-footer />
</x-app-layout>