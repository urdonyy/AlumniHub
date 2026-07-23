<x-app-layout>
    <x-slot name="title">Admin Inbox</x-slot>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">{{ __('Admin Inbox') }}</h2>
            <p class="text-sm text-red-900">{{ __('Pending items across the platform that need your review') }}</p>
        </div>
    </x-slot>

    <div class="pb-24">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Community Requests --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-red-900 to-red-800 px-6 py-4 text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-[#FFC107]">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide">{{ __('Community Requests') }}</h3>
                            <p class="text-xs text-red-100/80">{{ __('Batch communities awaiting approval') }}</p>
                        </div>
                    </div>
                    @if ($pendingCommunityRequests->isNotEmpty())
                        <span class="rounded-full bg-[#FFC107] px-2.5 py-0.5 text-xs font-bold text-red-900">
                            {{ $pendingCommunityRequests->count() }}
                        </span>
                    @endif
                </div>

                <ul class="divide-y divide-gray-100">
                    @forelse ($pendingCommunityRequests as $req)
                        <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-900/10 text-red-900">
                                    <i class="fa-solid fa-users text-sm"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ $req->name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        <span class="font-medium text-gray-700">{{ $req->requestor->name }}</span>
                                        <span class="text-gray-300">·</span> {{ __('Co-mods') }}:
                                        @foreach ($req->coModeratorInvites as $invite){{ $invite->invitedUser->name }}{{ !$loop->last ? ',' : '' }}@endforeach
                                        <span class="text-gray-300">·</span> {{ $req->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('admin.community-requests.show', $req) }}"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-md bg-red-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                <i class="fa-solid fa-gavel text-[11px]"></i>{{ __('Review') }}
                            </a>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No community requests pending review.') }}</li>
                    @endforelse
                </ul>

                @if ($pendingCommunityRequests->isNotEmpty())
                    <a href="{{ route('admin.community-requests.index') }}"
                        class="block border-t border-gray-100 px-6 py-3 text-xs font-semibold text-red-900 transition hover:bg-gray-50">
                        {{ __('View all community requests') }} →
                    </a>
                @endif
            </div>

            {{-- Verification Requests --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-red-900 to-red-800 px-6 py-4 text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-[#FFC107]">
                            <i class="fa-solid fa-id-card"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide">{{ __('Verification Requests') }}</h3>
                            <p class="text-xs text-red-100/80">{{ __('Accounts awaiting identity verification') }}</p>
                        </div>
                    </div>
                    @if ($pendingVerifications->isNotEmpty())
                        <span class="rounded-full bg-[#FFC107] px-2.5 py-0.5 text-xs font-bold text-red-900">
                            {{ $pendingVerifications->count() }}
                        </span>
                    @endif
                </div>

                <ul class="divide-y divide-gray-100">
                    @forelse ($pendingVerifications as $doc)
                        <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-900/10 text-red-900">
                                    <i class="fa-solid fa-user text-sm"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ $doc->user->name ?: __('(no name yet)') }}</p>
                                    <p class="mt-0.5 truncate text-xs text-gray-500">
                                        {{ $doc->user->email }}
                                        @if ($doc->user->program_course)<span class="text-gray-300">·</span> {{ $doc->user->program_course }}@endif
                                        @if ($doc->user->batch_year)<span class="text-gray-300">·</span> {{ __('Batch') }} {{ $doc->user->batch_year }}@endif
                                        <span class="text-gray-300">·</span> {{ $doc->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('admin.verifications.index') }}"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-md bg-red-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                <i class="fa-solid fa-magnifying-glass text-[11px]"></i>{{ __('Review') }}
                            </a>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No verification requests pending review.') }}</li>
                    @endforelse
                </ul>

                @if ($pendingVerifications->isNotEmpty())
                    <a href="{{ route('admin.verifications.index') }}"
                        class="block border-t border-gray-100 px-6 py-3 text-xs font-semibold text-red-900 transition hover:bg-gray-50">
                        {{ __('View all verification requests') }} →
                    </a>
                @endif
            </div>

            {{-- Reported Posts --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-red-900 to-red-800 px-6 py-4 text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-[#FFC107]">
                            <i class="fa-solid fa-flag"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide">{{ __('Reported Posts') }}</h3>
                            <p class="text-xs text-red-100/80">{{ __('Posts flagged by the community') }}</p>
                        </div>
                    </div>
                    @if ($reportedPosts->isNotEmpty())
                        <span class="rounded-full bg-[#FFC107] px-2.5 py-0.5 text-xs font-bold text-red-900">
                            {{ $reportedPosts->count() }}
                        </span>
                    @endif
                </div>

                <ul class="divide-y divide-gray-100">
                    @forelse ($reportedPosts as $post)
                        <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                                    <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900">
                                        {{ $post->title ?: \Illuminate\Support\Str::limit(strip_tags($post->body_html ?? $post->body_markdown), 60) ?: __('(untitled post)') }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        <span class="font-medium text-gray-700">{{ $post->user->name }}</span>
                                        @if ($post->community)<span class="text-gray-300">·</span> {{ $post->community->name }}@endif
                                        <span class="text-gray-300">·</span> <span class="font-medium text-rose-600">{{ $post->reports_count }} {{ __('reports') }}</span>
                                        <span class="text-gray-300">·</span> {{ $post->flagged_at?->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('admin.reports.index') }}"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-md bg-red-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                <i class="fa-solid fa-gavel text-[11px]"></i>{{ __('Review') }}
                            </a>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-sm text-gray-500">{{ __('No reported posts pending review.') }}</li>
                    @endforelse
                </ul>

                @if ($reportedPosts->isNotEmpty())
                    <a href="{{ route('admin.reports.index') }}"
                        class="block border-t border-gray-100 px-6 py-3 text-xs font-semibold text-red-900 transition hover:bg-gray-50">
                        {{ __('View all reported posts') }} →
                    </a>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
