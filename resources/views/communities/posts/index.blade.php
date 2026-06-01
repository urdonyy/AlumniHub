<x-app-layout>
    <x-slot name="title">Posts</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $community->name }} - Posts
            </h2>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ openComposer: false }">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (auth()->check() && $community->members()->where('user_id', auth()->id())->exists())
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <button type="button" @click="openComposer = true"
                        class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-left text-gray-500 transition hover:bg-gray-100">
                        What's on your mind, {{ auth()->user()->name }}?
                    </button>
                </div>
            @endif

            @if ($posts->isEmpty())
                <div class="rounded-lg border border-gray-200 bg-white px-6 py-12 text-center shadow-sm">
                    <p class="text-gray-600">No posts yet. Be the first to share something!</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($posts as $post)
                        <x-post-card :post="$post" :community="$community" />
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>

        @if (auth()->check() && $community->members()->where('user_id', auth()->id())->exists())
            <div x-show="openComposer" x-transition.opacity @keydown.escape.window="openComposer = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" style="display: none;">
                <div @click.away="openComposer = false"
                    class="w-full max-w-2xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Create post</h3>
                        <button type="button" @click="openComposer = false"
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-xl leading-none text-gray-600 hover:bg-gray-200">
                            ×
                        </button>
                    </div>

                    <form method="post" action="{{ route('communities.posts.store', $community) }}"
                        enctype="multipart/form-data" class="max-h-[80vh] overflow-y-auto space-y-4 px-5 py-5">
                        @csrf

                        <p class="text-sm text-gray-500">Posting to <span class="font-medium text-gray-900">{{ $community->name }}</span></p>

                        <div>
                            <input type="text" name="title" value="{{ old('title') }}"
                                class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-red-900 focus:ring-red-900"
                                placeholder="Post title (optional)">
                        </div>

                        <div>
                            <textarea name="body_markdown" rows="6" required
                                class="w-full rounded-lg border border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-red-900 focus:ring-red-900"
                                placeholder="What's on your mind, {{ auth()->user()->name }}?">{{ old('body_markdown') }}</textarea>
                        </div>

                        @if(!empty($flairs) && $flairs->count() > 0)
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Flair tag</label>
                                <x-flair-selector :flairs="$flairs" :selected="old('flairs', [])" />
                            </div>
                        @endif

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Add images</label>
                            <input type="file" name="attachments[]" multiple accept="image/*"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-red-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-red-800">
                        </div>

                        <button type="submit"
                            class="w-full rounded-lg bg-red-900 px-4 py-3 font-semibold text-white transition hover:bg-red-800">
                            Post
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>