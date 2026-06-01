<x-app-layout>
    <x-slot name="title">Community Request</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Review request') }} · {{ $communityRequest->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $communityRequest->name }}</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('Requested by') }} {{ $communityRequest->requestor->name }} ·
                        {{ $communityRequest->created_at->diffForHumans() }}
                    </p>
                </div>

                <div class="space-y-4 px-6 py-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Description') }}</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $communityRequest->description }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Purpose') }}</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $communityRequest->purpose }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">
                            {{ $communityRequest->program_course }}
                        </span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">
                            {{ __('Section') }} {{ $communityRequest->year_section }}
                        </span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">
                            {{ __('Batch') }} {{ $communityRequest->batch_year }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h4 class="text-base font-semibold text-gray-900">{{ __('Moderator team') }}</h4>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $communityRequest->requestor->name }}</p>
                            <p class="text-xs text-gray-500">{{ __('Requestor (will be senior moderator)') }}</p>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ __('Confirmed') }}</span>
                    </li>
                    @foreach ($communityRequest->coModeratorInvites as $invite)
                        <li class="flex items-center justify-between px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $invite->invitedUser->name }}</p>
                                <p class="text-xs text-gray-500">{{ __('Invited co-moderator') }}</p>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-wide
                                @switch($invite->status)
                                    @case('accepted') text-emerald-700 @break
                                    @case('declined') text-rose-700 @break
                                    @default text-amber-700
                                @endswitch">
                                {{ $invite->status }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($communityRequest->status === 'pending_admin')
                <div class="grid gap-4 md:grid-cols-2">
                    <form method="POST" action="{{ route('admin.community-requests.approve', $communityRequest) }}"
                        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        @csrf
                        <h5 class="text-sm font-semibold text-emerald-800">{{ __('Approve') }}</h5>
                        <label class="mt-3 block text-xs font-medium text-gray-700">{{ __('Optional note') }}</label>
                        <textarea name="admin_note" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        <x-input-error :messages="$errors->get('admin_note')" class="mt-2" />
                        <button type="submit"
                            class="mt-3 inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            {{ __('Approve and create community') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.community-requests.reject', $communityRequest) }}"
                        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        @csrf
                        <h5 class="text-sm font-semibold text-rose-800">{{ __('Reject') }}</h5>
                        <label class="mt-3 block text-xs font-medium text-gray-700">{{ __('Reason (required, 5+ chars)') }}</label>
                        <textarea name="admin_note" rows="3" required minlength="5"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500"></textarea>
                        <x-input-error :messages="$errors->get('admin_note')" class="mt-2" />
                        <button type="submit"
                            class="mt-3 inline-flex items-center rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                            {{ __('Reject') }}
                        </button>
                    </form>
                </div>
            @elseif ($communityRequest->status === 'approved')
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ __('Already approved.') }}
                    @if ($communityRequest->community)
                        <a href="{{ route('communities.show', $communityRequest->community) }}" class="font-semibold underline">{{ __('Open community') }}</a>
                    @endif
                </div>
            @elseif ($communityRequest->status === 'rejected')
                <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ __('Rejected.') }} {{ $communityRequest->admin_note }}
                </div>
            @else
                <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ __('Awaiting co-moderator responses. This request is not yet eligible for admin decision.') }}
                </div>
            @endif

            <div>
                <a href="{{ route('admin.community-requests.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    {{ __('Back to queue') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
