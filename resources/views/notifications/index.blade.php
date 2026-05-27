<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight">
                {{ __('Notifications') }}
            </h2>

            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-secondary-button type="submit">
                        Mark all as read
                    </x-secondary-button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($notifications->count() === 0)
                        <p class="text-sm text-gray-600">No notifications yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($notifications as $notification)
                                @php
                                    $data = $notification->data ?? [];
                                    $message = $data['message'] ?? $notification->type;
                                    $url = $data['url'] ?? null;
                                    if (!empty($data['community_id']) && !empty($data['post_id'])) {
                                        $url = route('communities.posts.open', [
                                            'community' => $data['community_id'],
                                            'post' => $data['post_id'],
                                        ]);
                                    }
                                    $type = $data['type'] ?? null;
                                    $postTitle = $data['post_title'] ?? null;
                                    $commentPreview = $data['comment_preview'] ?? null;
                                @endphp

                                <div class="flex items-start justify-between gap-4 rounded-md border border-gray-200 p-3 {{ $notification->read_at ? 'bg-white' : 'bg-amber-50' }}">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if ($type === 'post_commented')
                                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">Comment</span>
                                            @elseif ($type === 'comment_replied')
                                                <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-[11px] font-semibold text-violet-700 ring-1 ring-inset ring-violet-200">Reply</span>
                                            @elseif ($type === 'post_liked')
                                                <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">Like</span>
                                            @endif

                                            @if ($postTitle)
                                                <span class="text-xs font-semibold text-gray-700 truncate">{{ $postTitle }}</span>
                                            @endif
                                        </div>

                                        <p class="text-sm text-gray-900 truncate">{{ $message }}</p>

                                        @if ($commentPreview)
                                            <p class="mt-1 text-xs text-gray-600 line-clamp-2">“{{ $commentPreview }}”</p>
                                        @endif

                                        <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at?->diffForHumans() }}</p>

                                        @if ($url)
                                            <a href="{{ $url }}" class="text-xs text-red-900 underline">View</a>
                                        @endif
                                    </div>

                                    @if (! $notification->read_at)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-gray-600 hover:text-gray-900">Mark read</button>
                                        </form>
                                    @endif
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
