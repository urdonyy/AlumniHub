<x-app-layout>
    <x-slot name="title">Deleted Posts</x-slot>
    <x-slot name="header">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1.5 text-sm">
            <a href="{{ route('profiles.show', auth()->id()) }}" class="font-semibold text-red-900/70 transition hover:text-red-900">{{ __('Profile') }}</a>
            <svg class="h-4 w-4 shrink-0 text-red-900/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="font-semibold text-red-900">{{ __('Deleted Posts') }}</span>
        </nav>
    </x-slot>

    <div class="py-6"
        x-data="{
            managing: false,
            selected: [],
            allIds: @js($posts->pluck('id')),
            baseUrl: '{{ url('/posts') }}',
            csrf: document.querySelector('meta[name=csrf-token]')?.content,
            async bulkRequest(action) {
                const suffix = action === 'restore' ? '/restore' : '/force';
                const method = action === 'restore' ? 'POST' : 'DELETE';
                await Promise.all(this.selected.map(id =>
                    fetch(`${this.baseUrl}/${id}${suffix}`, {
                        method,
                        headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' }
                    })
                ));
                window.location.reload();
            }
        }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    @if (session('success'))
                        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    <p class="mb-4 text-xs text-gray-500">Deleted posts are only visible to you. Permanently deleted posts are gone for everyone.</p>

                    @if ($posts->count() === 0)
                        <p class="rounded-lg border border-dashed border-gray-300 px-3 py-8 text-center text-sm text-gray-500">
                            Your deleted posts bin is empty.
                        </p>
                    @else

                        {{-- Toolbar --}}
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('posts.force-delete', '__all__') }}"
                                    x-data
                                    @submit.prevent="
                                        if (!confirm('Permanently delete all posts in trash?')) return;
                                        $el.submit();
                                    ">
                                    @csrf
                                    @method('DELETE')
                                    {{-- We handle empty-all differently — individual force deletes in JS --}}
                                    <button type="button"
                                        @click="
                                            if (!confirm('Permanently delete all posts in trash?')) return;
                                            bulkRequest.call({selected: allIds, baseUrl, csrf}, 'force')
                                        "
                                        class="text-xs font-semibold text-gray-500 hover:text-red-700 transition">
                                        Empty trash
                                    </button>
                                </form>
                            </div>

                            <div class="flex items-center gap-3">
                                <label x-show="managing" class="flex items-center gap-1.5 cursor-pointer" style="display:none">
                                    <input type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-red-900 accent-red-900 cursor-pointer focus:ring-yellow-500"
                                        :checked="allIds.length > 0 && selected.length === allIds.length"
                                        x-effect="$el.indeterminate = selected.length > 0 && selected.length < allIds.length"
                                        @change="selected = $event.target.checked ? [...allIds] : []">
                                    <span class="text-xs font-semibold text-gray-500">Select all</span>
                                </label>

                                <button type="button"
                                    @click="managing = !managing; selected = []"
                                    class="text-xs font-semibold"
                                    :class="managing ? 'text-red-900 hover:text-red-800' : 'text-gray-500 hover:text-gray-700'">
                                    <span x-text="managing ? '{{ __('Cancel') }}' : '{{ __('Manage') }}'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Manage action bar --}}
                        <div x-show="managing && selected.length > 0"
                            x-transition
                            class="mb-3 flex items-center justify-between gap-3 rounded-lg border border-gray-100 bg-gray-50 px-4 py-2.5"
                            style="display:none">
                            <p class="text-sm text-gray-700">
                                <span x-text="selected.length"></span> selected
                            </p>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="bulkRequest('restore')"
                                    title="{{ __('Restore') }}"
                                    class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:bg-gray-200 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                                    </svg>
                                </button>
                                <button type="button" @click="bulkRequest('force')"
                                    class="rounded-md bg-red-900 px-3 py-1.5 text-xs font-semibold tracking-widest text-white hover:bg-red-800">
                                    {{ __('Delete permanently') }}
                                </button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach ($posts as $post)
                                <div class="flex items-start gap-3 rounded-md border border-gray-200 bg-white p-3 transition"
                                    :class="managing && selected.includes({{ $post->id }}) ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white'">

                                    {{-- Checkbox (manage mode) --}}
                                    <div x-show="managing" class="shrink-0 pt-0.5" style="display:none">
                                        <input type="checkbox"
                                            value="{{ $post->id }}"
                                            x-model="selected"
                                            class="h-4 w-4 rounded border-gray-300 text-red-900 accent-red-900 cursor-pointer focus:ring-yellow-500">
                                    </div>

                                    {{-- Content --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if ($post->post_type === 'event')
                                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200">📅 Event</span>
                                            @elseif ($post->post_type === 'media')
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 ring-1 ring-inset ring-gray-200">📷 Media</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600 ring-1 ring-inset ring-gray-200">📝 Text</span>
                                            @endif
                                            @if ($post->community)
                                                <span class="text-[11px] text-gray-500">{{ $post->community->name }}</span>
                                            @endif
                                        </div>

                                        @if ($post->title)
                                            <p class="mt-1 text-sm font-semibold text-gray-700 line-through">{{ $post->title }}</p>
                                        @endif

                                        @if (filled($post->body_markdown))
                                            <p class="mt-0.5 text-xs text-gray-500 line-clamp-2">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($post->body_html ?? $post->body_markdown), 120) }}
                                            </p>
                                        @endif

                                        <p class="mt-1 text-xs text-gray-400">
                                            Deleted {{ $post->trashed_at->diffForHumans() }}
                                            @if ($post->community)
                                                &middot; {{ $post->community->name }}
                                            @endif
                                        </p>
                                    </div>

                                    {{-- Per-item actions --}}
                                    <div class="flex items-center gap-2 shrink-0" x-show="!managing">
                                        <form method="POST" action="{{ route('posts.restore', $post) }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-gray-600 hover:text-gray-900">Restore</button>
                                        </form>
                                        <form method="POST" action="{{ route('posts.force-delete', $post) }}"
                                            onsubmit="return confirm('Permanently delete this post? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-900">Delete forever</button>
                                        </form>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $posts->links() }}
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
