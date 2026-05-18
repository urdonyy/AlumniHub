<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Post - {{ $community->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="px-6 py-6">
                    <form method="post" action="{{ route('communities.posts.update', [$community, $post]) }}"
                        enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('put')

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Title (Optional)')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                placeholder="Give your post a title" value="{{ old('title', $post->title) }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <!-- Body Markdown -->
                        <div>
                            <x-input-label for="body_markdown" :value="__('Content')" />
                            <textarea id="body_markdown" name="body_markdown" rows="10"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Write your post in Markdown..."
                                required>{{ old('body_markdown', $post->body_markdown) }}</textarea>
                            <p class="mt-2 text-xs text-gray-500">Supports Markdown formatting</p>
                            <x-input-error class="mt-2" :messages="$errors->get('body_markdown')" />
                        </div>

                        <!-- Current Flairs -->
                        <div>
                            <x-input-label for="flairs" :value="__('Flairs (Tags)')" />
                            <x-flair-selector :flairs="$flairs" :selected="$post->flairs->pluck('id')->toArray()" />
                            <x-input-error class="mt-2" :messages="$errors->get('flairs')" />
                        </div>

                        <!-- Current Attachments -->
                        @if ($post->media->count() > 0)
                            <div>
                                <x-input-label :value="__('Current Attachments')" />
                                <div class="mt-2 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($post->media as $media)
                                        <div class="relative overflow-hidden rounded-lg border border-gray-200">
                                            @if (str_starts_with($media->file_type, 'image/'))
                                                <img src="{{ $media->url }}" alt="Post attachment" class="h-32 w-full object-cover">
                                            @else
                                                <div class="flex h-32 w-full items-center justify-center bg-gray-100">
                                                    <span class="text-gray-600">📎 File</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Add New Attachments -->
                        <div>
                            <x-input-label for="attachments" :value="__('Add New Attachments (Images only, max 5MB each)')" />
                            <input type="file" id="attachments" name="attachments[]" multiple accept="image/*"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <p class="mt-2 text-xs text-gray-500">You can upload additional images</p>
                            <x-input-error class="mt-2" :messages="$errors->get('attachments')" />
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4">
                            <x-primary-button>{{ __('Update Post') }}</x-primary-button>
                            <a href="{{ route('communities.posts.show', [$community, $post]) }}"
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