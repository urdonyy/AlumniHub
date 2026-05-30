<x-app-layout>
    <x-slot name="title">{{ $post->title }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">
                {{ $post->title ?? 'Post' }}
            </h2>
            @if (auth()->check() && (auth()->id() === $post->user_id || $community->isModerator(auth()->user())))
                <div class="flex gap-2">
                    <a href="{{ route('communities.posts.edit', [$community, $post]) }}"
                        class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Edit
                    </a>
                    <form method="post" action="{{ route('communities.posts.destroy', [$community, $post]) }}"
                        class="inline" onsubmit="return confirm('Delete this post?');">
                        @csrf
                        @method('delete')
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <!-- Post Header -->
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="space-y-4 px-6 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <img src="{{ $post->user->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                alt="{{ $post->user->name }}" class="h-12 w-12 rounded-full object-cover">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $post->user->name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right text-sm text-gray-600">
                            <p>{{ $post->view_count }} views</p>
                        </div>
                    </div>

                    <!-- Flairs -->
                    @if ($post->flairs->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach ($post->flairs as $flair)
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset"
                                    style="background-color: {{ $flair->color ?? '#e5e7eb' }}20; color: {{ $flair->color ?? '#6b7280' }}; border-color: {{ $flair->color ?? '#d1d5db' }};">
                                    @if ($flair->icon)
                                        <span class="mr-1">{{ $flair->icon }}</span>
                                    @endif
                                    {{ $flair->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Title -->
                    @if ($post->title)
                        <h1 class="text-3xl font-bold text-gray-900">{{ $post->title }}</h1>
                    @endif
                </div>
            </div>

            <!-- Post Content -->
            <div class="mt-6 rounded-lg border border-gray-200 bg-white px-6 py-6 shadow-sm">
                <div class="prose prose-sm max-w-none text-gray-700">
                    {!! $post->body_html !!}
                </div>

                <!-- Media/Attachments -->
                @if ($post->media->count() > 0)
                    <div class="mt-6 space-y-4">
                        <h3 class="font-semibold text-gray-900">Attachments</h3>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($post->media as $media)
                                <a href="{{ $media->url }}" target="_blank"
                                    class="overflow-hidden rounded-lg border border-gray-200">
                                    @if (str_starts_with($media->file_type, 'image/'))
                                        <img src="{{ $media->url }}" alt="Post attachment"
                                            class="h-48 w-full object-cover hover:opacity-90">
                                    @else
                                        <div class="flex h-48 w-full items-center justify-center bg-gray-100">
                                            <span class="text-gray-600">📎 File</span>
                                        </div>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Back Link -->
            <div class="mt-6">
                <a href="{{ route('communities.posts.index', $community) }}"
                    class="text-indigo-600 hover:text-indigo-700 font-semibold">
                    ← Back to Posts
                </a>
            </div>
        </div>
    </div>
</x-app-layout>