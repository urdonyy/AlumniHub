<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight">
                {{ __('Notifications') }}
            </h2>

            <div class="flex items-center gap-3">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <x-secondary-button type="submit">Mark all as read</x-secondary-button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6"
        x-data="{
            managing: false,
            selected: [],
            baseUrl: '{{ url('/notifications') }}',
            csrf: document.querySelector('meta[name=csrf-token]')?.content,
            async bulkRequest(action) {
                const method = action === 'delete' ? 'DELETE' : 'POST';
                const suffix = action === 'read' ? '/read' : action === 'unread' ? '/unread' : '';
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

                    @if ($notifications->count() === 0 && $filter === 'all')
                        <p class="text-sm text-gray-600">No notifications yet.</p>
                    @else

                        {{-- Toolbar --}}
                        <div class="mb-3 flex items-center justify-between gap-3">

                            {{-- Left: filter dropdown + trash icon --}}
                            <div class="flex items-center gap-2">

                                {{-- Gmail-style filter dropdown --}}
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button type="button"
                                        @click="open = !open"
                                        class="flex items-center gap-1 rounded border border-gray-300 bg-white px-2 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
                                        {{-- selector icon --}}
                                        <svg class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="1.5">
                                            <rect x="1.75" y="1.75" width="12.5" height="12.5" rx="1.5"/>
                                        </svg>
                                        <span class="ml-0.5">{{ ucfirst($filter) }}</span>
                                        {{-- chevron --}}
                                        <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 10 10" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 3.5l3 3 3-3"/>
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="absolute left-0 top-full z-20 mt-1 w-32 rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
                                        style="display:none">
                                        @foreach (['all' => 'All', 'read' => 'Read', 'unread' => 'Unread', 'starred' => 'Starred'] as $value => $label)
                                            <a href="{{ route('notifications.index', ['filter' => $value]) }}"
                                                class="flex items-center gap-2 px-3 py-2 text-sm {{ $filter === $value ? 'font-semibold text-gray-900' : 'text-gray-700 hover:bg-gray-50' }}">
                                                @if ($filter === $value)
                                                    <svg class="h-3.5 w-3.5 text-red-900 shrink-0" viewBox="0 0 16 16" fill="currentColor">
                                                        <path d="M13.78 4.22a.75.75 0 0 1 0 1.06l-7.25 7.25a.75.75 0 0 1-1.06 0L2.22 9.28a.75.75 0 0 1 1.06-1.06L6 10.94l6.72-6.72a.75.75 0 0 1 1.06 0z"/>
                                                    </svg>
                                                @else
                                                    <span class="h-3.5 w-3.5 shrink-0"></span>
                                                @endif
                                                {{ $label }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Trash icon --}}
                                <a href="{{ route('notifications.trash') }}"
                                    class="relative flex items-center justify-center h-8 w-8 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition"
                                    title="Trash">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>
                                    </svg>
                                    @if ($trashedCount > 0)
                                        <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-gray-400 text-[9px] font-bold text-white leading-none">
                                            {{ $trashedCount > 9 ? '9+' : $trashedCount }}
                                        </span>
                                    @endif
                                </a>

                            </div>

                            {{-- Right: Manage toggle --}}
                            @if ($notifications->count() > 0)
                                <button type="button"
                                    @click="managing = !managing; selected = []"
                                    class="text-xs font-semibold"
                                    :class="managing ? 'text-red-700 hover:text-red-900' : 'text-gray-500 hover:text-gray-700'">
                                    <span x-text="managing ? '{{ __('Cancel') }}' : '{{ __('Manage') }}'"></span>
                                </button>
                            @endif
                        </div>

                        {{-- Manage action bar (top, visible when items selected) --}}
                        <div x-show="managing && selected.length > 0"
                            x-transition
                            class="mb-3 flex items-center justify-between gap-3 rounded-lg border border-gray-100 bg-gray-50 px-4 py-2.5"
                            style="display:none">
                            <p class="text-sm text-gray-700">
                                <span x-text="selected.length"></span> selected
                            </p>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="bulkRequest('read')"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold tracking-widest text-gray-700 hover:bg-gray-100">
                                    {{ __('Mark as read') }}
                                </button>
                                <button type="button" @click="bulkRequest('unread')"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold tracking-widest text-gray-700 hover:bg-gray-100">
                                    {{ __('Mark as unread') }}
                                </button>
                                <button type="button" @click="bulkRequest('delete')"
                                    class="rounded-md bg-red-900 px-3 py-1.5 text-xs font-semibold tracking-widest text-white hover:bg-red-800">
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        </div>

                        @if ($notifications->count() === 0)
                            <p class="rounded-lg border border-dashed border-gray-300 px-3 py-6 text-center text-sm text-gray-500">
                                No {{ $filter !== 'all' ? $filter : '' }} notifications.
                            </p>
                        @else
                            <div class="space-y-3">
                                @foreach ($notifications as $notification)
                                    @php
                                        $data           = $notification->data ?? [];
                                        $message        = $data['message'] ?? $notification->type;
                                        $url            = $data['url'] ?? null;
                                        if (!empty($data['community_id']) && !empty($data['post_id'])) {
                                            $url = route('communities.posts.open', [
                                                'community' => $data['community_id'],
                                                'post'      => $data['post_id'],
                                            ]);
                                        }
                                        $type           = $data['type'] ?? null;
                                        $postTitle      = $data['post_title'] ?? null;
                                        $commentPreview = $data['comment_preview'] ?? null;
                                        $redirectPath   = $url
                                            ? (parse_url($url, PHP_URL_PATH) . (parse_url($url, PHP_URL_QUERY) ? '?' . parse_url($url, PHP_URL_QUERY) : ''))
                                            : null;
                                    @endphp

                                    <div class="flex items-start gap-3 rounded-md border p-3 transition"
                                        :class="managing && selected.includes('{{ $notification->id }}') ? 'border-red-300 bg-red-50' : '{{ $notification->read_at ? 'border-gray-200 bg-white' : 'border-gray-200 bg-amber-50' }}'">

                                        {{-- Checkbox (manage mode) --}}
                                        <div x-show="managing" class="shrink-0 pt-0.5" style="display:none">
                                            <input type="checkbox"
                                                value="{{ $notification->id }}"
                                                x-model="selected"
                                                class="h-4 w-4 rounded border-gray-300 text-red-900 accent-red-900 cursor-pointer focus:ring-yellow-500">
                                        </div>

                                        {{-- Content --}}
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                @if ($type === 'post_commented')
                                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">Comment</span>
                                                @elseif ($type === 'comment_replied')
                                                    <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-[11px] font-semibold text-violet-700 ring-1 ring-inset ring-violet-200">Reply</span>
                                                @elseif ($type === 'post_liked')
                                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">Like</span>
                                                @elseif ($type === 'connection_invite')
                                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">Invite</span>
                                                @elseif ($type === 'connection_accepted')
                                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Accepted</span>
                                                @elseif ($type === 'connection_declined')
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-700 ring-1 ring-inset ring-gray-200">Declined</span>
                                                @elseif ($type === 'co_moderator_invite')
                                                    <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-[11px] font-semibold text-violet-700 ring-1 ring-inset ring-violet-200">Co-mod invite</span>
                                                @elseif ($type === 'co_moderator_accepted')
                                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Co-mod accepted</span>
                                                @elseif ($type === 'co_moderator_declined')
                                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">Co-mod declined</span>
                                                @elseif ($type === 'community_creation_pending_review')
                                                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200">Review needed</span>
                                                @elseif ($type === 'community_creation_approved')
                                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Community approved</span>
                                                @elseif ($type === 'community_creation_rejected')
                                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">Community rejected</span>
                                                @elseif ($type === 'community_creation_cancelled')
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-700 ring-1 ring-inset ring-gray-300">Request terminated</span>
                                                @elseif ($type === 'join_request_submitted')
                                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">Join request</span>
                                                @elseif ($type === 'join_request_accepted')
                                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Join accepted</span>
                                                @elseif ($type === 'join_request_ignored')
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-700 ring-1 ring-inset ring-gray-200">Join ignored</span>
                                                @elseif ($type === 'community_member_removed')
                                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">Removed</span>
                                                @elseif ($type === 'community_post_removed')
                                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">Post removed</span>
                                                @endif

                                                @if ($postTitle)
                                                    <span class="text-xs font-semibold text-gray-700 truncate">{{ $postTitle }}</span>
                                                @endif
                                            </div>

                                            <p class="text-sm text-gray-900 truncate">{{ $message }}</p>

                                            @if ($commentPreview)
                                                <p class="mt-1 text-xs text-gray-600 line-clamp-2">"{{ $commentPreview }}"</p>
                                            @endif

                                            <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at?->diffForHumans() }}</p>

                                            <div class="mt-1 flex items-center gap-1.5" x-show="!managing">
                                                @if ($url)
                                                    @if (! $notification->read_at)
                                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                                                            @csrf
                                                            <input type="hidden" name="redirect_to" value="{{ $redirectPath }}">
                                                            <button type="submit" class="text-xs text-red-900 hover:underline">View</button>
                                                        </form>
                                                    @else
                                                        <a href="{{ $url }}" class="text-xs text-red-900 hover:underline">View</a>
                                                    @endif
                                                @endif

                                                @if (! $notification->read_at)
                                                    @if ($url)
                                                        <span class="text-xs text-gray-300">|</span>
                                                    @endif
                                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-xs text-gray-500 hover:text-gray-800">Mark read</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Right side actions --}}
                                        <div class="flex flex-col items-end gap-1 shrink-0"
                                            x-data="{ starred: {{ $notification->starred_at ? 'true' : 'false' }} }">

                                            {{-- Star toggle --}}
                                            <button type="button"
                                                @click="
                                                    fetch('{{ route('notifications.star', $notification->id) }}', {
                                                        method: 'POST',
                                                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                                                    }).then(r => r.json()).then(d => starred = d.starred)
                                                "
                                                class="flex items-center justify-center h-7 w-7 rounded-full transition hover:bg-gray-100"
                                                :title="starred ? 'Unstar' : 'Star'">
                                                <svg class="h-4 w-4 transition" :class="starred ? 'text-red-700' : 'text-gray-300 hover:text-gray-400'" viewBox="0 0 24 24">
                                                    <path :fill="starred ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                            </button>

                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
