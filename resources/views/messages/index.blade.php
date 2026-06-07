<x-app-layout>
    <x-slot name="title">Messages</x-slot>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                {{ __('Messages') }}
            </h2>
            <p class="text-sm text-red-900">{{ __('Chat one-on-one with your connections.') }}</p>
        </div>
    </x-slot>

    <div class="pb-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Existing conversations --}}
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-5 py-3.5">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">{{ __('Conversations') }}</h3>
                </div>

                @forelse ($conversations as $conversation)
                    @php
                        $partner = $conversation->otherParticipantFor($authUser);
                        $last = $conversation->latestMessage->first();
                        $unread = $unreadByConversation[$conversation->id] ?? 0;
                    @endphp
                    @if ($partner)
                        <a href="{{ route('messages.show', $partner->id) }}"
                            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition border-b border-gray-50 last:border-b-0">
                            <img src="{{ $partner->profileAvatarUrl() }}" alt="{{ $partner->name }}"
                                class="h-11 w-11 shrink-0 rounded-full object-cover ring-1 ring-gray-200">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $partner->name }}</p>
                                    @if ($last)
                                        <span class="shrink-0 text-xs text-gray-400">{{ $last->created_at->diffForHumans(null, true) }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <p class="truncate text-sm {{ $unread > 0 ? 'font-medium text-gray-900' : 'text-gray-500' }}">
                                        @if ($last)
                                            @if ($last->sender_id === $authUser->id)<span class="text-gray-400">You: </span>@endif
                                            {{ \Illuminate\Support\Str::limit($last->body, 48) }}
                                        @else
                                            <span class="italic text-gray-400">{{ __('No messages yet') }}</span>
                                        @endif
                                    </p>
                                    @if ($unread > 0)
                                        <span class="shrink-0 inline-flex items-center justify-center rounded-full bg-red-900 px-2 py-0.5 text-xs font-semibold text-white">{{ $unread }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endif
                @empty
                    <p class="px-5 py-6 text-sm text-gray-500">{{ __('No conversations yet. Start one with a connection below.') }}</p>
                @endforelse
            </section>

            {{-- Start a new chat with a connection --}}
            @if ($newChatPartners->isNotEmpty())
                <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 px-5 py-3.5">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">{{ __('Start a new chat') }}</h3>
                    </div>
                    @foreach ($newChatPartners as $partner)
                        <a href="{{ route('messages.show', $partner->id) }}"
                            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition border-b border-gray-50 last:border-b-0">
                            <img src="{{ $partner->profileAvatarUrl() }}" alt="{{ $partner->name }}"
                                class="h-10 w-10 shrink-0 rounded-full object-cover ring-1 ring-gray-200">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900">{{ $partner->name }}</p>
                                <p class="truncate text-xs text-gray-500">
                                    {{ $partner->batch_year ? 'Batch ' . $partner->batch_year : '' }}
                                </p>
                            </div>
                            <i class="fa-regular fa-message text-gray-400"></i>
                        </a>
                    @endforeach
                </section>
            @endif

        </div>
    </div>
</x-app-layout>
<x-footer />