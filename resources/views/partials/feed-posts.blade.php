@forelse($posts as $post)
    @php
        $postApiUrl = $post->community
            ? route('communities.posts.api', ['community' => $post->community, 'post' => $post])
            : route('posts.api', ['post' => $post]);
        $postLikeUrl = $post->community
            ? route('communities.posts.like', ['community' => $post->community, 'post' => $post])
            : route('posts.like', ['post' => $post]);
    @endphp
    <article x-data="postCard({{ $post->id }}, {{ $post->like_count }}, {{ $post->comments_count ?? 0 }}, '{{ $postApiUrl }}', '{{ $postLikeUrl }}', {{ $post->isLikedByAuthUser() ? 'true' : 'false' }})"
        @click="openPostModal($event)"
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm cursor-pointer transition hover:shadow-md hover:border-gray-300 min-w-0">

        <!-- Post Header -->
        <div class="p-4 pb-3">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <img src="{{ $post->user->profileAvatarUrl() }}"
                        alt="{{ $post->user->name }}"
                        class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover"
                        onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $post->user->name }}</p>
                        @php
                            $batchLabel = $post->user->batch_year ? 'Batch ' . $post->user->batch_year : null;
                            $programAbbr = null;
                            if ($post->user->program_course && preg_match('/\(([^)]+)\)$/', $post->user->program_course, $m)) {
                                $programAbbr = $m[1];
                            }
                        @endphp
                        <p class="text-xs text-gray-500">
                            @if($batchLabel || $programAbbr)
                                {{ implode(' · ', array_filter([$batchLabel, $programAbbr])) }}
                                <span class="mx-1">·</span>
                            @endif
                            {{ $post->community?->name ?? __('Post') }}
                            @if($post->published_at)
                                · {{ $post->published_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                </div>
                @php
                    $visibilityConfig = match($post->visibility) {
                        'public'      => ['bg-green-50 text-green-700 ring-green-200', 'Public'],
                        'connections' => ['bg-blue-50 text-blue-700 ring-blue-200', 'Connections'],
                        default       => ['bg-gray-100 text-gray-600 ring-gray-200', 'Members'],
                    };
                @endphp
                <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $visibilityConfig[0] }}">
                    {{ $visibilityConfig[1] }}
                </span>
            </div>

            @if($post->flairs->count() > 0)
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach($post->flairs as $flair)
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                            style="background-color: {{ $flair->color ? $flair->color . '20' : '#f3f4f6' }}; color: {{ $flair->color ?? '#374151' }}; border: 1px solid {{ $flair->color ?? '#e5e7eb' }};">
                            @if($flair->icon)<span>{{ $flair->icon }}</span>@endif
                            {{ $flair->name }}
                        </span>
                    @endforeach
                </div>
            @endif

            @if($post->title)
                <h4 class="mt-2 text-base font-semibold text-gray-900">
                    {{ $post->title }}
                </h4>
            @endif

            @if(filled($post->body_html ?? $post->body_markdown))
                <p class="mt-2 text-sm leading-6 text-gray-700 break-words"
                    x-ref="postBody"
                    :class="isBodyExpanded ? '' : 'line-clamp-3'">
                    {{ strip_tags($post->body_html ?? $post->body_markdown) }}
                </p>
                <button type="button"
                    x-show="isBodyOverflowing"
                    @click.stop="toggleBody()"
                    class="mt-1 text-sm font-semibold text-red-900 hover:underline">
                    <span x-text="isBodyExpanded ? 'See less' : 'See more'"></span>
                </button>
            @endif

            @if($post->post_type === 'event' && $post->event)
                @php $ev = $post->event; @endphp
                <div class="mt-3 rounded-xl border border-indigo-100 bg-indigo-50/60 px-3.5 py-3">
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-indigo-700 ring-1 ring-indigo-100">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 text-sm">
                            <p class="font-semibold text-indigo-900">{{ $ev->isOnline() ? 'Online event' : 'In-person event' }}</p>
                            <p class="text-gray-700">
                                {{ $ev->starts_at?->format('M j, Y · g:i A') }}
                                @if($ev->ends_at) <span class="text-gray-400">–</span> {{ $ev->ends_at->format('M j, Y · g:i A') }} @endif
                            </p>
                            @unless($ev->isOnline())
                                <p class="mt-0.5 text-gray-700">📍 {{ $ev->address }}@if($ev->venue) <span class="text-gray-400">·</span> {{ $ev->venue }}@endif</p>
                            @endunless
                            @if($ev->external_link)
                                <a href="{{ $ev->external_link }}" target="_blank" rel="noopener" @click.stop
                                    class="mt-0.5 inline-block break-all text-indigo-700 hover:underline">{{ $ev->external_link }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Post Media -->
        @if($post->media->count() > 0)
            <div class="relative overflow-hidden bg-gray-50 max-h-80">
                <img src="{{ $post->media->first()->url }}"
                    alt="Post image"
                    class="w-full max-h-80 object-contain">
                @if($post->media->count() > 1)
                    <div class="absolute bottom-2 right-2 rounded-full bg-black/60 px-2.5 py-1 text-xs font-medium text-white">
                        +{{ $post->media->count() - 1 }} more
                    </div>
                @endif
            </div>
        @endif

        <!-- Post Footer -->
        <div class="border-t border-gray-100 px-2 py-1">
            <div class="flex">
                @if (auth()->user()->isVerified())
                    <button type="button" @click.stop="toggleLike()"
                        :disabled="isLikingLoading"
                        :class="{ 'text-red-700': isLiked, 'text-gray-600': !isLiked, 'opacity-60': isLikingLoading }"
                        class="flex flex-1 items-center justify-center gap-2 rounded-lg py-2 text-sm font-medium transition hover:bg-gray-50">
                        <svg class="h-4 w-4" :fill="isLiked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span x-text="likeCount + (likeCount === 1 ? ' Like' : ' Likes')"></span>
                    </button>
                    <button type="button" @click.stop="openPostModal($event)"
                        class="flex flex-1 items-center justify-center gap-2 rounded-lg py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2l-4 4z" />
                        </svg>
                        <span x-text="commentCount + (commentCount === 1 ? ' Comment' : ' Comments')"></span>
                    </button>
                @else
                    <span class="flex flex-1 items-center justify-center gap-2 py-2 text-sm font-medium text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span x-text="likeCount + (likeCount === 1 ? ' Like' : ' Likes')"></span>
                    </span>
                    <span class="flex flex-1 items-center justify-center gap-2 py-2 text-sm font-medium text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2l-4 4z" />
                        </svg>
                        <span x-text="commentCount + (commentCount === 1 ? ' Comment' : ' Comments')"></span>
                    </span>
                @endif
            </div>
        </div>
    </article>
@empty
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center">
        <p class="text-sm text-gray-500">No posts match the selected filters.</p>
    </div>
@endforelse
