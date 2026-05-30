<x-app-layout>
    <x-slot name="title">New Community Request</x-slot>
    <x-slot name="header">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1.5 text-sm">
            <a href="{{ route('communities.index') }}" class="font-semibold text-red-900/70 transition hover:text-red-900">{{ __('Communities') }}</a>
            <svg class="h-4 w-4 shrink-0 text-red-900/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="font-semibold text-red-900">{{ __('Request a New Community') }}</span>
        </nav>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if ($existingCommunity)
                <div class="rounded-2xl border border-amber-300 bg-amber-50 px-6 py-5 shadow-sm">
                    <h3 class="text-base font-semibold text-amber-900">
                        {{ __('A community with this name already exists') }}
                    </h3>
                    <p class="mt-1 text-sm text-amber-800">
                        {{ __('Instead of creating a duplicate, join ":name" — you can ask its moderators to accept your request.', ['name' => $existingCommunity->name]) }}
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('communities.show', $existingCommunity) }}"
                            class="inline-flex items-center rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">
                            {{ __('Open community') }}
                        </a>
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Community details') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Use the exact naming format: "PROGRAM Y-S Batch YYYY" (e.g., "DICT 3-3 Batch 2026"). The program and batch must match your registered details.') }}
                    </p>
                </div>

                @php
                    $programCode = \App\Http\Requests\StoreCommunityCreationRequest::extractProgramCode($user->program_course);
                @endphp

                @php
                    $oldYearSection = old('year_section', '');
                    [$oldYear, $oldSection] = array_pad(explode('-', $oldYearSection, 2), 2, '');
                @endphp

                <form method="POST" action="{{ route('communities.requests.store') }}"
                    x-data="{
                        year: @js($oldYear),
                        section: @js($oldSection),
                        get yearSection() { return (this.year && this.section) ? this.year + '-' + this.section : ''; }
                    }"
                    class="space-y-6 border-t border-gray-200 px-6 py-6">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label for="program_code" :value="__('Program')" />
                            <x-text-input id="program_code" type="text" class="mt-1 block w-full bg-gray-50"
                                :value="$programCode ?? '—'" readonly />
                            <p class="mt-1 text-xs text-gray-500">{{ $user->program_course ?? __('Set your program in your profile.') }}</p>
                        </div>

                        <div>
                            <x-input-label :value="__('Year & Section')" />
                            <div class="mt-1 flex items-center gap-2">
                                <x-text-input type="text" inputmode="numeric" maxlength="1"
                                    class="block w-full text-center" x-model="year"
                                    required placeholder="Y" pattern="\d" aria-label="{{ __('Year') }}" />
                                <span class="text-gray-500 select-none">-</span>
                                <x-text-input type="text" inputmode="numeric" maxlength="1"
                                    class="block w-full text-center" x-model="section"
                                    required placeholder="S" pattern="\d" aria-label="{{ __('Section') }}" />
                            </div>
                            <input type="hidden" name="year_section" :value="yearSection">
                            <p class="mt-1 text-xs text-gray-500">{{ __('Format: Y-S (e.g., 3-3)') }}</p>
                            <x-input-error :messages="$errors->get('year_section')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="batch_year_display" :value="__('Batch')" />
                            <x-text-input id="batch_year_display" type="text" class="mt-1 block w-full bg-gray-50"
                                :value="$user->batch_year ?? '—'" readonly />
                            <p class="mt-1 text-xs text-gray-500">{{ __('From your registration.') }}</p>
                        </div>
                    </div>

                    @if (! $programCode || ! $user->batch_year)
                        <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            {{ __('Your program or batch is missing from your profile. Update it before submitting a request.') }}
                        </div>
                    @else
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ __('This will create:') }}
                            <span class="font-semibold">{{ $programCode }} <span x-text="yearSection || 'Y-S'"></span> Batch {{ $user->batch_year }}</span>
                        </div>
                    @endif

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3" required
                            x-data x-init="$el.style.height='auto'; $el.style.height = $el.scrollHeight + 'px'"
                            @input="$el.style.height='auto'; $el.style.height = $el.scrollHeight + 'px'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 resize-none overflow-hidden">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="purpose" :value="__('Purpose')" />
                        <textarea id="purpose" name="purpose" rows="3" required
                            x-data x-init="$el.style.height='auto'; $el.style.height = $el.scrollHeight + 'px'"
                            @input="$el.style.height='auto'; $el.style.height = $el.scrollHeight + 'px'"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 resize-none overflow-hidden">{{ old('purpose') }}</textarea>
                        <x-input-error :messages="$errors->get('purpose')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label :value="__('Co-moderators (select exactly 2 from your connections)')" />
                        <p class="mt-1 text-xs text-gray-500">
                            {{ __('Both co-moderators must accept the role before your request reaches the admin.') }}
                        </p>

                        @if ($connections->isEmpty())
                            <div class="mt-3 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                                {{ __('You have no verified connections yet. Connect with two verified users before submitting a request.') }}
                            </div>
                        @else
                            <div x-data="{ selected: @js(array_map('strval', old('co_moderator_ids', []))) }">
                                <div class="mt-3 grid gap-2 sm:grid-cols-2 max-h-72 overflow-y-auto rounded-md border border-gray-200 p-3">
                                    @foreach ($connections as $connection)
                                        <label :class="(!selected.includes('{{ $connection->id }}') && selected.length >= 2)
                                                        ? 'opacity-50 cursor-not-allowed pointer-events-none'
                                                        : 'hover:bg-gray-50 cursor-pointer'"
                                               class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 transition-opacity">
                                            <input type="checkbox" name="co_moderator_ids[]" value="{{ $connection->id }}"
                                                class="h-4 w-4 rounded border-gray-300 text-red-900 accent-red-900 focus:ring-yellow-500"
                                                @change="if ($el.checked && selected.length >= 2) { $el.checked = false; } else if ($el.checked) { selected.push('{{ $connection->id }}'); } else { selected = selected.filter(id => id !== '{{ $connection->id }}'); }"
                                                :disabled="!selected.includes('{{ $connection->id }}') && selected.length >= 2"
                                                @checked(in_array($connection->id, old('co_moderator_ids', []), false))>
                                            <span class="text-sm text-gray-800">{{ $connection->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="mt-1.5 text-xs" :class="selected.length === 2 ? 'text-emerald-600' : 'text-gray-400'">
                                    <span x-text="selected.length"></span>/2 selected
                                </p>
                            </div>
                        @endif
                        <x-input-error :messages="$errors->get('co_moderator_ids')" class="mt-2" />
                        <x-input-error :messages="$errors->get('co_moderator_ids.0')" class="mt-2" />
                        <x-input-error :messages="$errors->get('co_moderator_ids.1')" class="mt-2" />
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button" x-data @click="$dispatch('open-modal', 'request-info')"
                            class="inline-flex items-center gap-1.5 self-start text-sm font-medium text-red-900/70 transition hover:text-red-900">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('How does this work?') }}
                        </button>
                        <div class="flex items-center justify-end gap-3">
                            <x-ghost-button :href="route('communities.index')">{{ __('Cancel') }}</x-ghost-button>
                            <x-tertiary-button>{{ __('Submit request') }}</x-tertiary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-modal name="request-info" maxWidth="sm" focusable>
        <div class="relative flex flex-col items-center p-6 text-center">
            <button type="button" x-on:click="$dispatch('close')"
                class="absolute right-3 top-3 rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                aria-label="{{ __('Close') }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-900">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h2 class="mt-3 text-base font-semibold text-gray-900">{{ __('How does this work?') }}</h2>

            <p class="mt-1.5 text-sm leading-6 text-gray-600">
                {{ __('Submit a program-batch community for admin review. Two co-moderators from your connections must accept first.') }}
            </p>

            <x-tertiary-button x-on:click="$dispatch('close')" class="mt-5">{{ __('Got it') }}</x-tertiary-button>
        </div>
    </x-modal>
</x-app-layout>
