<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Request a New Community') }}
        </h2>
        <p class="text-sm text-gray-600">{{ __('Submit a program-batch community for admin review. Two co-moderators from your connections must accept first.') }}</p>
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

                <form method="POST" action="{{ route('communities.requests.store') }}" class="space-y-6 border-t border-gray-200 px-6 py-6">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Community name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name')" required placeholder="DICT 3-3 Batch 2026" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        <p class="mt-1 text-xs text-gray-500">
                            {{ __('Your program: :p · Your batch: :b', ['p' => $user->program_course ?? '—', 'b' => $user->batch_year ?? '—']) }}
                        </p>
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="purpose" :value="__('Purpose')" />
                        <textarea id="purpose" name="purpose" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('purpose') }}</textarea>
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
                            <div class="mt-3 grid gap-2 sm:grid-cols-2 max-h-72 overflow-y-auto rounded-md border border-gray-200 p-3">
                                @foreach ($connections as $connection)
                                    <label class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 hover:bg-gray-50">
                                        <input type="checkbox" name="co_moderator_ids[]" value="{{ $connection->id }}"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            @checked(in_array($connection->id, old('co_moderator_ids', []), false))>
                                        <span class="text-sm text-gray-800">{{ $connection->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        <x-input-error :messages="$errors->get('co_moderator_ids')" class="mt-2" />
                        <x-input-error :messages="$errors->get('co_moderator_ids.0')" class="mt-2" />
                        <x-input-error :messages="$errors->get('co_moderator_ids.1')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('communities.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button>{{ __('Submit request') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
