<x-app-layout>
    <x-slot name="title">Admin Inbox</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Admin Inbox') }}</h2>
        <p class="text-sm text-gray-600">{{ __('Pending items that require your review.') }}</p>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Community Requests --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-6 py-5">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Community Requests') }}</h3>
                        <p class="mt-0.5 text-sm text-gray-500">{{ __('Batch community requests awaiting approval.') }}</p>
                    </div>
                    @if ($pendingCommunityRequests->isNotEmpty())
                        <span class="inline-flex shrink-0 items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-200">
                            {{ $pendingCommunityRequests->count() }} {{ __('pending') }}
                        </span>
                    @endif
                </div>

                <ul class="divide-y divide-gray-200">
                    @forelse ($pendingCommunityRequests as $req)
                        <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $req->name }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ __('By') }} {{ $req->requestor->name }}
                                    · {{ __('Co-mods') }}:
                                    @foreach ($req->coModeratorInvites as $invite)
                                        {{ $invite->invitedUser->name }}{{ !$loop->last ? ',' : '' }}
                                    @endforeach
                                    · {{ $req->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <a href="{{ route('admin.community-requests.show', $req) }}"
                                class="inline-flex shrink-0 items-center rounded-md bg-indigo-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-600">
                                {{ __('Review') }}
                            </a>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-sm text-gray-500">
                            {{ __('No community requests pending review.') }}
                        </li>
                    @endforelse
                </ul>

                @if ($pendingCommunityRequests->isNotEmpty())
                    <div class="border-t border-gray-100 px-6 py-3">
                        <a href="{{ route('admin.community-requests.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline">
                            {{ __('View all community requests →') }}
                        </a>
                    </div>
                @endif
            </div>

            {{-- Verification Requests --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-6 py-5">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Verification Requests') }}</h3>
                        <p class="mt-0.5 text-sm text-gray-500">{{ __('Alumni accounts awaiting identity verification.') }}</p>
                    </div>
                    @if ($pendingVerifications->isNotEmpty())
                        <span class="inline-flex shrink-0 items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-200">
                            {{ $pendingVerifications->count() }} {{ __('pending') }}
                        </span>
                    @endif
                </div>

                <ul class="divide-y divide-gray-200">
                    @forelse ($pendingVerifications as $doc)
                        <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $doc->user->name }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ $doc->user->email }}
                                    @if ($doc->user->program_course)
                                        · {{ $doc->user->program_course }}
                                    @endif
                                    @if ($doc->user->batch_year)
                                        · {{ __('Batch') }} {{ $doc->user->batch_year }}
                                    @endif
                                    · {{ $doc->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <a href="{{ route('admin.verifications.index') }}"
                                class="inline-flex shrink-0 items-center rounded-md bg-indigo-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-600">
                                {{ __('Review') }}
                            </a>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-sm text-gray-500">
                            {{ __('No verification requests pending review.') }}
                        </li>
                    @endforelse
                </ul>

                @if ($pendingVerifications->isNotEmpty())
                    <div class="border-t border-gray-100 px-6 py-3">
                        <a href="{{ route('admin.verifications.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline">
                            {{ __('View all verification requests →') }}
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
