                        <div x-data="feedController(@js(($availableFlairs ?? collect())->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'icon' => $f->icon])->values()), @js($selectedFlairIds ?? []), {{ isset($posts) && $posts->hasMorePages() ? 'true' : 'false' }}, {{ isset($posts) ? $posts->currentPage() : 1 }}, '{{ $feedUrl ?? route('feed.posts') }}')"
                            x-init="initScroll()" class="space-y-3">

                        {{-- Flair feed filter --}}
                        @if(isset($availableFlairs) && $availableFlairs->isNotEmpty())
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Filter by topic</span>
                                    <svg x-show="loading" class="h-3.5 w-3.5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24" style="display:none;">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400" x-show="selected.length > 0" x-text="`${selected.length}/3 active`" style="display:none;"></span>
                                    <button x-show="selected.length > 0" @click="clearAll()"
                                        class="text-xs font-medium text-red-900 hover:underline" style="display:none;">Clear</button>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="flair in visibleFlairs" :key="flair.id">
                                    <button type="button"
                                        @click="toggle(flair.id)"
                                        :disabled="!canSelect(flair.id)"
                                        class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium transition"
                                        :class="{
                                            'border-red-900 bg-red-900 text-white shadow-sm': isSelected(flair.id),
                                            'border-gray-300 bg-white text-gray-700 hover:border-gray-400 hover:bg-gray-50': !isSelected(flair.id) && canSelect(flair.id),
                                            'border-gray-200 bg-gray-50 text-gray-300 cursor-not-allowed': !canSelect(flair.id)
                                        }">
                                        <span x-show="flair.icon" x-text="flair.icon" class="leading-none"></span>
                                        <span x-text="flair.name"></span>
                                    </button>
                                </template>
                                <template x-if="flairs.length > 8">
                                    <button type="button" @click="expanded = !expanded"
                                        class="inline-flex items-center gap-1 rounded-full border border-dashed border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-500 hover:bg-gray-50 transition">
                                        <span x-text="expanded ? 'Show less' : `+${flairs.length - 8} more`"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        @endif

                        <div id="feed-posts-container" class="space-y-3">
                        @if(isset($posts))
                            @include('partials.feed-posts', ['posts' => $posts])
                        @else
                            @foreach ($feedCards as $card)
                                <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                                    <div class="p-5">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h3 class="text-base font-semibold text-gray-900">{{ $card['author'] }}</h3>
                                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ $card['meta'] }}</p>
                                            </div>
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                                {{ __('Placeholder') }}
                                            </span>
                                        </div>

                                        <p class="mt-4 text-sm leading-6 text-gray-700">{{ $card['content'] }}</p>
                                    </div>

                                    <div class="mt-5 flex flex-wrap gap-2 border-t border-gray-100 px-5 py-3 text-xs">
                                        <button type="button" disabled class="rounded-md border border-gray-200 px-3 py-1 font-semibold text-gray-500">{{ __('Like') }}</button>
                                        <button type="button" disabled class="rounded-md border border-gray-200 px-3 py-1 font-semibold text-gray-500">{{ __('Comment') }}</button>
                                        <button type="button" disabled class="rounded-md border border-gray-200 px-3 py-1 font-semibold text-gray-500">{{ __('Share') }}</button>
                                    </div>
                                </article>
                            @endforeach
                        @endif

                        </div>{{-- end feed-posts-container --}}

                        {{-- Infinite scroll sentinel + states --}}
                        <div x-ref="sentinel" aria-hidden="true" class="h-px"></div>
                        <div x-show="loadingMore" class="flex justify-center py-6" style="display:none;">
                            <svg class="h-6 w-6 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                        </div>
                        <div x-show="reachedEnd" class="py-6 text-center text-xs text-gray-400" style="display:none;">
                            You're all caught up
                        </div>
                        </div>{{-- end feedController --}}
