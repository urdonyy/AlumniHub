<x-app-layout>
    <x-slot name="title">Community Request</x-slot>
    <x-slot name="header">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1.5 text-sm">
            <a href="{{ route('admin.communities.index') }}"
                class="font-semibold text-red-900/70 transition hover:text-red-900">{{ __('Communities') }}</a>
            <svg class="h-4 w-4 shrink-0 text-red-900/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('admin.community-requests.index') }}"
                class="font-semibold text-red-900/70 transition hover:text-red-900">{{ __('Community Requests') }}</a>
            <svg class="h-4 w-4 shrink-0 text-red-900/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="font-semibold text-red-900">{{ $communityRequest->name }}</span>
        </nav>
    </x-slot>

    <div class="pb-24">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Request overview --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center gap-3 bg-gradient-to-r from-red-900 to-red-800 px-6 py-4 text-white">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 text-[#FFC107]">
                        <i class="fa-solid fa-users-rectangle"></i>
                    </span>
                    <div class="min-w-0">
                        <h3 class="truncate text-base font-bold">{{ $communityRequest->name }}</h3>
                        <p class="text-xs text-red-100/80">
                            {{ __('Requested by') }} {{ $communityRequest->requestor->name }}
                            <span class="text-red-200/60">·</span>
                            {{ $communityRequest->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>

                <div class="space-y-5 px-6 py-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Description') }}</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $communityRequest->description }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Purpose') }}</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $communityRequest->purpose }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 border-t border-gray-100 pt-4 text-xs">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-900/5 px-2.5 py-1 font-medium text-red-900">
                            <i class="fa-solid fa-graduation-cap text-[10px]"></i>{{ $communityRequest->program_course }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-900/5 px-2.5 py-1 font-medium text-red-900">
                            <i class="fa-solid fa-layer-group text-[10px]"></i>{{ __('Section') }} {{ $communityRequest->year_section }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-900/5 px-2.5 py-1 font-medium text-red-900">
                            <i class="fa-solid fa-calendar text-[10px]"></i>{{ __('Batch') }} {{ $communityRequest->batch_year }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Moderator team --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                        <i class="fa-solid fa-user-shield text-sm"></i>
                    </span>
                    <h4 class="text-sm font-bold uppercase tracking-wide text-gray-700">{{ __('Moderator Team') }}</h4>
                </div>
                <ul class="divide-y divide-gray-100">
                    <li class="flex items-center justify-between gap-3 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#FFC107]/20 text-red-900">
                                <i class="fa-solid fa-crown text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $communityRequest->requestor->name }}</p>
                                <p class="text-xs text-gray-500">{{ __('Requestor (will be senior moderator)') }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                            <i class="fa-solid fa-circle-check text-[10px]"></i>{{ __('Confirmed') }}
                        </span>
                    </li>
                    @foreach ($communityRequest->coModeratorInvites as $invite)
                        <li class="flex items-center justify-between gap-3 px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                                    <i class="fa-solid fa-user text-xs"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $invite->invitedUser->name }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Invited co-moderator') }}</p>
                                </div>
                            </div>
                            @php
                                $inviteStyle = [
                                    'accepted' => ['bg-emerald-100 text-emerald-700', 'fa-circle-check'],
                                    'declined' => ['bg-rose-100 text-rose-700', 'fa-circle-xmark'],
                                ][$invite->status] ?? ['bg-amber-100 text-amber-700', 'fa-clock'];
                            @endphp
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $inviteStyle[0] }}">
                                <i class="fa-solid {{ $inviteStyle[1] }} text-[10px]"></i>{{ ucfirst($invite->status) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Decision --}}
            @if ($communityRequest->status === 'pending_admin')
                <div class="grid gap-4 md:grid-cols-2">
                    <form method="POST" action="{{ route('admin.community-requests.approve', $communityRequest) }}"
                        class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
                        @csrf
                        <h5 class="flex items-center gap-1.5 text-sm font-bold text-emerald-700">
                            <i class="fa-solid fa-check"></i>{{ __('Approve') }}
                        </h5>
                        <label class="mt-3 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Optional note') }}</label>
                        <textarea name="admin_note" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        <x-input-error :messages="$errors->get('admin_note')" class="mt-2" />
                        <button type="submit"
                            class="mt-3 inline-flex w-full items-center justify-center gap-1.5 rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            <i class="fa-solid fa-circle-plus"></i>{{ __('Approve & create') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.community-requests.reject', $communityRequest) }}"
                        class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm">
                        @csrf
                        <h5 class="flex items-center gap-1.5 text-sm font-bold text-rose-700">
                            <i class="fa-solid fa-xmark"></i>{{ __('Reject') }}
                        </h5>
                        <label class="mt-3 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Reason (required, 5+ chars)') }}</label>
                        <textarea name="admin_note" rows="3" required minlength="5"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"></textarea>
                        <x-input-error :messages="$errors->get('admin_note')" class="mt-2" />
                        <button type="submit"
                            class="mt-3 inline-flex w-full items-center justify-center gap-1.5 rounded-md bg-rose-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                            <i class="fa-solid fa-ban"></i>{{ __('Reject') }}
                        </button>
                    </form>
                </div>
            @elseif ($communityRequest->status === 'approved')
                <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>
                        {{ __('Already approved.') }}
                        @if ($communityRequest->community)
                            <a href="{{ route('communities.show', $communityRequest->community) }}" class="font-semibold underline">{{ __('Open community') }}</a>
                        @endif
                    </span>
                </div>
            @elseif ($communityRequest->status === 'rejected')
                <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <i class="fa-solid fa-circle-xmark mt-0.5"></i>
                    <span>{{ __('Rejected.') }} {{ $communityRequest->admin_note }}</span>
                </div>
            @else
                <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <i class="fa-solid fa-clock mt-0.5"></i>
                    <span>{{ __('Awaiting co-moderator responses. This request is not yet eligible for admin decision.') }}</span>
                </div>
            @endif

            <div>
                <a href="{{ route('admin.community-requests.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>{{ __('Back to queue') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
