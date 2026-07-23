<x-app-layout>
    <x-slot name="title">Community Requests</x-slot>
    <x-slot name="header">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1.5 text-sm">
            <a href="{{ route('admin.communities.index') }}"
                class="font-semibold text-red-900/70 transition hover:text-red-900">{{ __('Communities') }}</a>
            <svg class="h-4 w-4 shrink-0 text-red-900/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="font-semibold text-red-900">{{ __('Community Requests') }}</span>
        </nav>
    </x-slot>

    <div class="pb-24">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Status flashes --}}
            @if (session('status') === 'community-request-approved')
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ __('Request approved and community created.') }}
                </div>
            @elseif (session('status') === 'community-request-rejected')
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ __('Request rejected.') }}
                </div>
            @endif

            {{-- Pending review --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-red-900 to-red-800 px-6 py-4 text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-[#FFC107]">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide">{{ __('Pending Review') }}</h3>
                            <p class="text-xs text-red-100/80">{{ __('Awaiting your decision') }}</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-[#FFC107] px-2.5 py-0.5 text-xs font-bold text-red-900">
                        {{ $requests->total() ?? $requests->count() }}
                    </span>
                </div>

                <ul class="divide-y divide-gray-100">
                    @forelse ($requests as $req)
                        <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-900/10 text-red-900">
                                    <i class="fa-solid fa-users text-sm"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ $req->name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        <span class="font-medium text-gray-700">{{ $req->requestor->name }}</span>
                                        <span class="text-gray-300">·</span>
                                        {{ __('Co-mods') }}:
                                        @forelse ($req->coModeratorInvites as $invite)
                                            {{ $invite->invitedUser->name }}{{ ! $loop->last ? ',' : '' }}
                                        @empty
                                            {{ __('none') }}
                                        @endforelse
                                        <span class="text-gray-300">·</span>
                                        {{ $req->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('admin.community-requests.show', $req) }}"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-md bg-red-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                <i class="fa-solid fa-gavel text-[11px]"></i>{{ __('Review') }}
                            </a>
                        </li>
                    @empty
                        <li class="px-6 py-12 text-center">
                            <i class="fa-solid fa-inbox text-3xl text-gray-300"></i>
                            <p class="mt-3 text-sm text-gray-500">{{ __('No requests waiting for review.') }}</p>
                        </li>
                    @endforelse
                </ul>

                @if ($requests->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">{{ $requests->links() }}</div>
                @endif
            </div>

            {{-- Recent decisions --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                        <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                    </span>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-gray-700">{{ __('Recent Decisions') }}</h3>
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse ($history as $req)
                        <li class="flex items-center justify-between gap-3 px-6 py-3 text-sm">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-gray-900">{{ $req->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ __('Decided by') }} {{ $req->admin?->name ?? '—' }}
                                    <span class="text-gray-300">·</span>
                                    {{ $req->decided_at?->diffForHumans() }}
                                </p>
                            </div>
                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold
                                @if ($req->status === 'approved') bg-emerald-100 text-emerald-700
                                @else bg-rose-100 text-rose-700 @endif">
                                <i class="fa-solid {{ $req->status === 'approved' ? 'fa-circle-check' : 'fa-circle-xmark' }} text-[10px]"></i>
                                {{ ucfirst($req->status) }}
                            </span>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-xs text-gray-500">{{ __('No history yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
