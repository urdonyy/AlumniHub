<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Flair') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="px-6 py-6">
                    <form method="post" action="{{ route('admin.flairs.update', $flair) }}" class="space-y-6">
                        @csrf
                        @method('put')

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                placeholder="e.g., Job Referral" value="{{ old('name', $flair->name) }}" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <!-- Slug -->
                        <div>
                            <x-input-label for="slug" :value="__('Slug')" />
                            <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full"
                                placeholder="e.g., job-referral" value="{{ old('slug', $flair->slug) }}" required />
                            <p class="mt-2 text-xs text-gray-500">Used for URLs - lowercase, hyphens only</p>
                            <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                        </div>

                        <!-- Color -->
                        <div>
                            <x-input-label for="color" :value="__('Color (Hex)')" />
                            <div class="mt-1 flex gap-2">
                                <input type="color" id="color" name="color"
                                    class="h-10 w-16 rounded border border-gray-300"
                                    value="{{ old('color', $flair->color) }}" />
                                <x-text-input name="color" type="text" placeholder="#6366f1"
                                    class="block flex-1" value="{{ old('color', $flair->color) }}" />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('color')" />
                        </div>

                        <!-- Icon -->
                        <div>
                            <x-input-label for="icon" :value="__('Icon (Emoji or Text)')" />
                            <x-text-input id="icon" name="icon" type="text" class="mt-1 block w-full"
                                placeholder="e.g., 💼 or [JOB]" value="{{ old('icon', $flair->icon) }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('icon')" />
                        </div>

                        <!-- Is Sticky -->
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_sticky" value="1"
                                    class="rounded border-gray-300" @if (old('is_sticky', $flair->is_sticky)) checked @endif />
                                <span class="text-sm font-medium text-gray-700">Make this flair sticky (always show)</span>
                            </label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4">
                            <x-primary-button>{{ __('Update Flair') }}</x-primary-button>
                            <a href="{{ route('admin.flairs.index') }}"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
