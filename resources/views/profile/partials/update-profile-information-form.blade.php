<section>
    @php
        $bannerPreviewUrl = $user->banner_path
            ? \Illuminate\Support\Facades\Storage::url($user->banner_path)
            : asset('images/default-banner.svg');
        $avatarPreviewUrl = $user->avatar_path
            ? \Illuminate\Support\Facades\Storage::url($user->avatar_path)
            : asset('images/default-avatar.svg');
        $experienceValues = old(
            'experiences',
            $user->profileExperiences->map(
                fn($experience) => [
                    'id' => $experience->id,
                    'title' => $experience->title,
                    'organization' => $experience->organization,
                    'start_month' => $experience->start_date?->format('Y-m'),
                    'end_month' => $experience->end_date?->format('Y-m'),
                    'description' => $experience->description,
                ],
            )->values()->all(),
        );

        if (empty($experienceValues)) {
            $experienceValues = [[
                'id' => null,
                'title' => '',
                'organization' => '',
                'start_month' => '',
                'end_month' => '',
                'description' => '',
            ]];
        }
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Update your account details, avatar, and banner photo.') }}
        </p>
    </header>

    <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
        <span class="font-medium text-gray-900">{{ __('Status') }}:</span>
        <span
            class="ml-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $user->accountStatusBadgeClass() }}">
            {{ $user->accountStatusLabel() }}
        </span>
        <p class="mt-2 text-gray-600">
            {{ $user->isVerifiedAlumni() ? __('Your alumni verification is approved.') : __('You can keep using the system while your verification is pending review.') }}
        </p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6"
        id="profile-information-form">
        @csrf
        @method('patch')

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="first_name" :value="__('First Name')" />
                <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full"
                    :value="old('first_name', $user->first_name)" required autofocus autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            <div>
                <x-input-label for="last_name" :value="__('Last Name')" />
                <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full"
                    :value="old('last_name', $user->last_name)" required autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="batch_year" :value="__('Batch Year')" />
                <x-text-input id="batch_year" name="batch_year" type="number" class="mt-1 block w-full"
                    :value="old('batch_year', $user->batch_year)" min="2024" max="{{ now()->format('Y') }}" required />
                <x-input-error class="mt-2" :messages="$errors->get('batch_year')" />
            </div>

            <div>
                <x-input-label for="program_course" :value="__('Program / Course')" />
                <select id="program_course" name="program_course"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required>
                    <option value="" disabled {{ old('program_course', $user->program_course) ? '' : 'selected' }}>
                        {{ __('Select program/course') }}</option>
                    <option value="Diploma in Civil Engineering Technology (DCvET)" {{ old('program_course', $user->program_course) === 'Diploma in Civil Engineering Technology (DCvET)' ? 'selected' : '' }}>
                        Diploma in Civil Engineering Technology (DCvET)</option>
                    <option value="Diploma in Computer Engineering Technology (DCET)" {{ old('program_course', $user->program_course) === 'Diploma in Computer Engineering Technology (DCET)' ? 'selected' : '' }}>
                        Diploma in Computer Engineering Technology (DCET)</option>
                    <option value="Diploma in Electrical Engineering Technology (DEET)" {{ old('program_course', $user->program_course) === 'Diploma in Electrical Engineering Technology (DEET)' ? 'selected' : '' }}>
                        Diploma in Electrical Engineering Technology (DEET)</option>
                    <option value="Diploma in Electronics Engineering Technology (DECET)" {{ old('program_course', $user->program_course) === 'Diploma in Electronics Engineering Technology (DECET)' ? 'selected' : '' }}>
                        Diploma in Electronics Engineering Technology (DECET)</option>
                    <option value="Diploma in Information Communication Technology (DICT)" {{ old('program_course', $user->program_course) === 'Diploma in Information Communication Technology (DICT)' ? 'selected' : '' }}>
                        Diploma in Information Communication Technology (DICT)</option>
                    <option value="Diploma in Mechanical Engineering Technology (DMET)" {{ old('program_course', $user->program_course) === 'Diploma in Mechanical Engineering Technology (DMET)' ? 'selected' : '' }}>
                        Diploma in Mechanical Engineering Technology (DMET)</option>
                    <option value="Diploma in Office Management Technology (DOMT)" {{ old('program_course', $user->program_course) === 'Diploma in Office Management Technology (DOMT)' ? 'selected' : '' }}>
                        Diploma in Office Management Technology (DOMT)</option>
                    <option value="Diploma in Railway Engineering Technology (DRET)" {{ old('program_course', $user->program_course) === 'Diploma in Railway Engineering Technology (DRET)' ? 'selected' : '' }}>
                        Diploma in Railway Engineering Technology (DRET)</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('program_course')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification"
                            class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-900">{{ __('Career Profile') }}</h3>
            <p class="mt-1 text-sm text-gray-600">{{ __('Add your skills and work experience to make your profile more discoverable.') }}</p>

            <div class="mt-4">
                <x-input-label for="skills" :value="__('Skills (comma-separated)')" />
                <x-text-input id="skills" name="skills" type="text" class="mt-1 block w-full"
                    :value="old('skills', data_get($user, 'skills'))" placeholder="{{ __('Laravel, UI Design, Embedded Systems') }}" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Example: Project Management, AutoCAD, Data Analysis') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('skills')" />
            </div>

            <div class="mt-6">
                <div class="flex items-center justify-between gap-3">
                    <x-input-label :value="__('Experience')" />
                    <button type="button" id="add-experience"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-700 transition hover:bg-gray-100">
                        {{ __('Add Experience') }}
                    </button>
                </div>

                <div id="experience-list" class="mt-3 space-y-4" data-next-index="{{ count($experienceValues) }}">
                    @foreach ($experienceValues as $index => $experience)
                        <div class="experience-item rounded-lg border border-gray-200 bg-white p-4">
                            <input type="hidden" name="experiences[{{ $index }}][id]"
                                value="{{ $experience['id'] ?? '' }}" />

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label :for="'experience_title_' . $index" :value="__('Role / Title')" />
                                    <x-text-input :id="'experience_title_' . $index"
                                        :name="'experiences[' . $index . '][title]'" type="text" class="mt-1 block w-full"
                                        :value="$experience['title'] ?? ''" />
                                    <x-input-error class="mt-2" :messages="$errors->get('experiences.' . $index . '.title')" />
                                </div>

                                <div>
                                    <x-input-label :for="'experience_organization_' . $index" :value="__('Organization')" />
                                    <x-text-input :id="'experience_organization_' . $index"
                                        :name="'experiences[' . $index . '][organization]'" type="text"
                                        class="mt-1 block w-full" :value="$experience['organization'] ?? ''" />
                                    <x-input-error class="mt-2" :messages="$errors->get('experiences.' . $index . '.organization')" />
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label :for="'experience_start_month_' . $index" :value="__('Start Month')" />
                                    <x-text-input :id="'experience_start_month_' . $index"
                                        :name="'experiences[' . $index . '][start_month]'" type="month"
                                        class="mt-1 block w-full" :value="$experience['start_month'] ?? ''" />
                                    <x-input-error class="mt-2" :messages="$errors->get('experiences.' . $index . '.start_month')" />
                                </div>

                                <div>
                                    <x-input-label :for="'experience_end_month_' . $index" :value="__('End Month')" />
                                    <x-text-input :id="'experience_end_month_' . $index"
                                        :name="'experiences[' . $index . '][end_month]'" type="month"
                                        class="mt-1 block w-full" :value="$experience['end_month'] ?? ''" />
                                    <x-input-error class="mt-2" :messages="$errors->get('experiences.' . $index . '.end_month')" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-input-label :for="'experience_description_' . $index" :value="__('Description')" />
                                <textarea id="experience_description_{{ $index }}" name="experiences[{{ $index }}][description]"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $experience['description'] ?? '' }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('experiences.' . $index . '.description')" />
                            </div>

                            <div class="mt-4 flex justify-end">
                                <button type="button"
                                    class="remove-experience inline-flex items-center rounded-md border border-rose-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-rose-700 transition hover:bg-rose-50">
                                    {{ __('Remove') }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div id="avatar-crop-modal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-4 py-6" role="dialog"
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

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    <template id="experience-template">
        <div class="experience-item rounded-lg border border-gray-200 bg-white p-4">
            <input type="hidden" name="experiences[__INDEX__][id]" value="" />

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-gray-700" for="experience_title___INDEX__">{{ __('Role / Title') }}</label>
                    <input id="experience_title___INDEX__" name="experiences[__INDEX__][title]" type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700" for="experience_organization___INDEX__">{{ __('Organization') }}</label>
                    <input id="experience_organization___INDEX__" name="experiences[__INDEX__][organization]" type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-gray-700" for="experience_start_month___INDEX__">{{ __('Start Month') }}</label>
                    <input id="experience_start_month___INDEX__" name="experiences[__INDEX__][start_month]" type="month"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700" for="experience_end_month___INDEX__">{{ __('End Month') }}</label>
                    <input id="experience_end_month___INDEX__" name="experiences[__INDEX__][end_month]" type="month"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
            </div>

            <div class="mt-4">
                <label class="text-sm font-medium text-gray-700" for="experience_description___INDEX__">{{ __('Description') }}</label>
                <textarea id="experience_description___INDEX__" name="experiences[__INDEX__][description]" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button"
                    class="remove-experience inline-flex items-center rounded-md border border-rose-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-rose-700 transition hover:bg-rose-50">
                    {{ __('Remove') }}
                </button>
            </div>
        </div>
    </template>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
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

        if (!bannerInput || !bannerPreview || !avatarInput || !avatarPreview || !cropModal || !closeCropModalBtn || !cancelCropBtn || !cropZoom || !cropCanvas || !applyCropBtn || !cropMessage) {
            return;
        }

        const context = cropCanvas.getContext('2d');
        let sourceImage = null;
        let sourceUrl = null;

        const openCropModal = () => {
            cropModal.classList.remove('hidden');
            cropModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        };

        const closeCropModal = () => {
            cropModal.classList.add('hidden');
            cropModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const experienceList = document.getElementById('experience-list');
        const addExperienceButton = document.getElementById('add-experience');
        const experienceTemplate = document.getElementById('experience-template');

        if (!experienceList || !addExperienceButton || !experienceTemplate) {
            return;
        }

        const refreshRemoveButtons = () => {
            const items = experienceList.querySelectorAll('.experience-item');
            items.forEach((item) => {
                const removeButton = item.querySelector('.remove-experience');
                if (!removeButton) {
                    return;
                }

                removeButton.classList.toggle('hidden', items.length === 1);
            });
        };

        const appendExperience = () => {
            const nextIndex = Number(experienceList.dataset.nextIndex || '0');
            const templateHtml = experienceTemplate.innerHTML.replaceAll('__INDEX__', String(nextIndex));
            experienceList.insertAdjacentHTML('beforeend', templateHtml);
            experienceList.dataset.nextIndex = String(nextIndex + 1);
            refreshRemoveButtons();
        };

        addExperienceButton.addEventListener('click', appendExperience);

        experienceList.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement) || !target.classList.contains('remove-experience')) {
                return;
            }

            const item = target.closest('.experience-item');
            if (item) {
                item.remove();
                refreshRemoveButtons();
            }
        });

        refreshRemoveButtons();
    });
</script>