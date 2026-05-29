<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Community Creation Requests') }}
        </h2>
        <p class="text-sm text-gray-600">{{ __('Requests awaiting admin review. Only requests whose co-moderators have all accepted appear here.') }}</p>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('status') === 'community-request-approved')
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ __('Request approved and community created.') }}
                </div>
            @endif

            @if (session('status') === 'community-request-rejected')
                <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ __('Request rejected.') }}
                </div>
            @endif

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Pending review') }}</h3>
                </div>

                <ul class="divide-y divide-gray-200">
                    @forelse ($requests as $req)
                        <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $req->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ __('By') }} {{ $req->requestor->name }} ·
                                    {{ __('Co-mods') }}:
                                    @foreach ($req->coModeratorInvites as $invite)
                                        <span>{{ $invite->invitedUser->name }}{{ ! $loop->last ? ',' : '' }}</span>
                                    @endforeach
                                    · {{ $req->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <a href="{{ route('admin.community-requests.show', $req) }}"
                                class="inline-flex items-center rounded-md bg-indigo-700 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-600">
                                {{ __('Review') }}
                            </a>
                        </li>
                    @empty
                        <li class="px-6 py-10 text-center text-sm text-gray-600">
                            {{ __('No requests waiting for review.') }}
                        </li>
                    @endforelse
                </ul>

                @if ($requests->hasPages())
                    <div class="px-6 py-4">{{ $requests->links() }}</div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Recent decisions') }}</h3>
                </div>
                <ul class="divide-y divide-gray-200">
                    @forelse ($history as $req)
                        <li class="flex items-center justify-between px-6 py-3 text-sm">
                            <div>
                                <p class="font-medium text-gray-900">{{ $req->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ __('Decided by') }} {{ $req->admin?->name ?? '—' }} ·
                                    {{ $req->decided_at?->diffForHumans() }}
                                </p>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                @if ($req->status === 'approved') bg-emerald-100 text-emerald-800
                                @else bg-rose-100 text-rose-800
                                @endif">
                                {{ ucfirst($req->status) }}
                            </span>
                        </li>
                    @empty
                        <li class="px-6 py-6 text-center text-xs text-gray-500">{{ __('No history yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
