<div class="rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow">
    <a href="{{ route('communities.posts.show', [$community, $post]) }}" class="block px-6 py-6 hover:no-underline">
        <!-- Header: Author & Date -->
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ $post->user->profile_photo_url ?? asset('images/default-avatar.png') }}"
                    alt="{{ $post->user->name }}" class="h-10 w-10 rounded-full object-cover">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-900">{{ $post->user->name }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
            @if ($post->pinned)
                <span class="ml-2 text-lg" title="Pinned">📌</span>
            @endif
        </div>

        <!-- Title -->
        @if ($post->title)
            <h3 class="mb-2 text-lg font-bold text-gray-900">{{ $post->title }}</h3>
        @endif

        <!-- Event details -->
        @if ($post->post_type === 'event' && $post->event)
            @php $ev = $post->event; @endphp
            <div class="mb-3 rounded-lg border border-indigo-100 bg-indigo-50/60 px-3 py-2 text-sm text-gray-700">
                <p class="font-semibold text-indigo-900">{{ $ev->isOnline() ? 'Online event' : 'In-person event' }}</p>
                <p>
                    {{ $ev->starts_at?->format('M j, Y · g:i A') }}
                    @if ($ev->ends_at) <span class="text-gray-400">–</span> {{ $ev->ends_at->format('M j, Y · g:i A') }} @endif
                </p>
                @unless ($ev->isOnline())
                    <p>📍 {{ $ev->address }}@if ($ev->venue) <span class="text-gray-400">·</span> {{ $ev->venue }}@endif</p>
                @endunless
            </div>
        @endif

        <!-- Excerpt -->
        @if (filled($post->body_html))
            <p class="mb-3 line-clamp-2 text-gray-600 break-words break-normal">
                {{ strip_tags($post->body_html) }}
            </p>
        @endif

        <!-- Flairs -->
        @if ($post->flairs->count() > 0)
            <div class="mb-3 flex flex-wrap gap-2">
                @foreach ($post->flairs->take(3) as $flair)
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset"
                        style="background-color: {{ $flair->color ?? '#e5e7eb' }}20; color: {{ $flair->color ?? '#6b7280' }}; border-color: {{ $flair->color ?? '#d1d5db' }};">
                        @if ($flair->icon)
                            <span class="mr-1">{{ $flair->icon }}</span>
                        @endif
                        {{ $flair->name }}
                    </span>
                @endforeach
                @if ($post->flairs->count() > 3)
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                        +{{ $post->flairs->count() - 3 }} more
                    </span>
                @endif
            </div>
        @endif

        <!-- Meta -->
        <div class="flex items-center gap-4 text-xs text-gray-500">
            <span>{{ $post->view_count ?? 0 }} views</span>
            <span>{{ $post->comment_count ?? 0 }} comments</span>
            @if ($post->media->count() > 0)
                <span>🖼️ {{ $post->media->count() }} image{{ $post->media->count() !== 1 ? 's' : '' }}</span>
            @endif
        </div>
    </a>

    @auth
        @if ($post->user_id !== auth()->id() && ($community->isModerator(auth()->user()) || auth()->user()->canManageCommunities()))
            <div class="border-t border-gray-100 px-6 py-2 flex justify-end">
                <form method="POST" action="{{ route('communities.mod.posts.destroy', [$community, $post]) }}">
                    @csrf
                    @method('delete')
                    <button type="submit"
                        onclick="return confirm('{{ __('Remove this post from the community?') }}')"
                        class="text-xs font-semibold text-rose-700 hover:text-rose-800 hover:underline">
                        {{ __('Remove (mod)') }}
                    </button>
                </form>
            </div>
        @endif
    @endauth
</div>