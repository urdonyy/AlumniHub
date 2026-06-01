<x-app-layout>
    <x-slot name="title">Trash</x-slot>
    <x-slot name="header">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1.5 text-sm">
            <a href="{{ route('notifications.index') }}" class="font-semibold text-red-900/70 transition hover:text-red-900">{{ __('Notifications') }}</a>
            <svg class="h-4 w-4 shrink-0 text-red-900/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="font-semibold text-red-900">{{ __('Trash') }}</span>
        </nav>
    </x-slot>

    <div class="py-6"
        x-data="{
            managing: false,
            selected: [],
            allIds: @js($notifications->pluck('id')),
            baseUrl: '{{ url('/notifications') }}',
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

                    <p class="mb-4 text-xs text-gray-500">Items are permanently deleted after 30 days.</p>

                    @if ($notifications->count() === 0)
                        <p class="rounded-lg border border-dashed border-gray-300 px-3 py-6 text-center text-sm text-gray-500">
                            Trash is empty.
                        </p>
                    @else

                        {{-- Toolbar --}}
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                {{-- Empty trash --}}
                                <form method="POST" action="{{ route('notifications.trash.empty') }}"
                                    onsubmit="return confirm('Permanently delete all items in trash?')">
                                    @csrf
                                    <button type="submit"
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
                                {{-- Restore --}}
                                <button type="button" @click="bulkRequest('restore')"
                                    title="{{ __('Restore') }}"
                                    class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:bg-gray-200 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                                    </svg>
                                </button>

                                {{-- Delete permanently --}}
                                <button type="button" @click="bulkRequest('force')"
                                    title="{{ __('Delete permanently') }}"
                                    class="rounded-md bg-red-900 px-3 py-1.5 text-xs font-semibold tracking-widest text-white hover:bg-red-800">
                                    {{ __('Delete permanently') }}
                                </button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach ($notifications as $notification)
                                @php
                                    $data           = $notification->data ?? [];
                                    $message        = $data['message'] ?? $notification->type;
                                    $type           = $data['type'] ?? null;
                                    $postTitle      = $data['post_title'] ?? null;
                                    $trashedAt      = \Carbon\Carbon::parse($notification->trashed_at);
                                    $daysLeft       = max(0, 30 - (int) now()->diffInDays($trashedAt));
                                @endphp

                                <div class="flex items-start gap-3 rounded-md border border-gray-200 bg-white p-3 transition"
                                    :class="managing && selected.includes('{{ $notification->id }}') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white'">

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

                                        <p class="text-sm text-gray-500 truncate line-through">{{ $message }}</p>

                                        <p class="text-xs text-gray-400 mt-1">
                                            Deleted {{ \Carbon\Carbon::parse($notification->trashed_at)->diffForHumans() }}
                                            &middot;
                                            {{ $daysLeft > 0 ? "auto-deletes in {$daysLeft}d" : 'deletes soon' }}
                                        </p>
                                    </div>

                                    {{-- Per-item actions (hidden in manage mode) --}}
                                    <div class="flex items-center gap-2 shrink-0" x-show="!managing">
                                        <form method="POST" action="{{ route('notifications.restore', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-gray-600 hover:text-gray-900">Restore</button>
                                        </form>
                                        <form method="POST" action="{{ route('notifications.force-destroy', $notification->id) }}"
                                            onsubmit="return confirm('Permanently delete this notification?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
