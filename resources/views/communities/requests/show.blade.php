<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Community request') }} · {{ $communityRequest->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('status') === 'community-request-submitted')
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ __('Request submitted. Both co-moderators must accept before admin review.') }}
                </div>
            @endif

            @if (session('status') === 'co-moderator-accepted')
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ __('You accepted the co-moderator role.') }}
                </div>
            @endif

            @if (session('status') === 'co-moderator-declined')
                <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ __('You declined the co-moderator invite.') }}
                </div>
            @endif

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $communityRequest->name }}</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __('Requested by') }} {{ $communityRequest->requestor->name }} · {{ $communityRequest->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset
                            @switch($communityRequest->status)
                                @case('pending_co_mods') bg-amber-50 text-amber-800 ring-amber-200 @break
                                @case('pending_admin') bg-blue-50 text-blue-800 ring-blue-200 @break
                                @case('approved') bg-emerald-50 text-emerald-800 ring-emerald-200 @break
                                @case('rejected') bg-rose-50 text-rose-800 ring-rose-200 @break
                                @default bg-gray-50 text-gray-700 ring-gray-200
                            @endswitch">
                            {{ str_replace('_', ' ', ucfirst($communityRequest->status)) }}
                        </span>
                    </div>
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
                            {{ __('Program') }}: {{ $communityRequest->program_course }}
                        </span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">
                            {{ __('Year/Section') }}: {{ $communityRequest->year_section }}
                        </span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">
                            {{ __('Batch') }}: {{ $communityRequest->batch_year }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h4 class="text-base font-semibold text-gray-900">{{ __('Co-moderator invites') }}</h4>
                </div>
                <ul class="divide-y divide-gray-200">
                    @foreach ($communityRequest->coModeratorInvites as $invite)
                        <li class="flex items-center justify-between px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $invite->invitedUser->name }}</p>
                                <p class="text-xs text-gray-500">
                                    @if ($invite->status === 'pending')
                                        {{ __('Awaiting response') }}
                                    @elseif ($invite->status === 'accepted')
                                        {{ __('Accepted :time', ['time' => $invite->responded_at?->diffForHumans()]) }}
                                    @else
                                        {{ __('Declined :time', ['time' => $invite->responded_at?->diffForHumans()]) }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                @if ($myInvite && $myInvite->id === $invite->id && $invite->status === 'pending')
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('community-invites.accept', $invite) }}">
                                            @csrf
                                            <x-primary-button>{{ __('Accept') }}</x-primary-button>
                                        </form>
                                        <form method="POST" action="{{ route('community-invites.decline', $invite) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center rounded-md border border-rose-300 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                                                {{ __('Decline') }}
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs font-semibold uppercase tracking-wide
                                        @switch($invite->status)
                                            @case('accepted') text-emerald-700 @break
                                            @case('declined') text-rose-700 @break
                                            @default text-amber-700
                                        @endswitch">
                                        {{ $invite->status }}
                                    </span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($communityRequest->status === 'approved' && $communityRequest->community)
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ __('This request was approved.') }}
                    <a href="{{ route('communities.show', $communityRequest->community) }}" class="font-semibold underline">
                        {{ __('Open community') }}
                    </a>
                </div>
            @endif

            @if ($communityRequest->status === 'rejected' && $communityRequest->admin_note)
                <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <p class="font-semibold">{{ __('Rejected by admin') }}</p>
                    <p class="mt-1">{{ $communityRequest->admin_note }}</p>
                </div>
            @endif

            <div>
                <a href="{{ route('communities.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    {{ __('Back to communities') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
