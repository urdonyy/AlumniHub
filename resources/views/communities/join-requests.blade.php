<x-app-layout>
    <x-slot name="title">{{ __('Pending Join Requests') }} · {{ $community->name }}</x-slot>
    <x-slot name="header">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1.5 text-sm">
            <a href="{{ route('communities.index') }}" class="font-semibold text-red-900/70 transition hover:text-red-900">{{ __('Communities') }}</a>
            <svg class="h-4 w-4 shrink-0 text-red-900/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('communities.show', $community) }}" class="font-semibold text-red-900/70 transition hover:text-red-900">{{ $community->name }}</a>
            <svg class="h-4 w-4 shrink-0 text-red-900/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="font-semibold text-red-900">{{ __('Pending Join Requests') }}</span>
        </nav>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h4 class="text-lg font-semibold text-gray-900">{{ __('Pending join requests') }}</h4>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Decide who joins. If a requestor is already a member of another program-batch community, you must wait for them to leave it before accepting.') }}</p>
                </div>
                @if ($pendingJoinRequests->isEmpty())
                    <p class="px-6 py-6 text-center text-sm text-gray-500">{{ __('No pending requests.') }}</p>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach ($pendingJoinRequests as $jr)
                            @php($otherPb = $jr->user?->programBatchCommunity())
                            <li class="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $jr->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $jr->created_at->diffForHumans() }}</p>
                                    @if ($otherPb && $otherPb->id !== $community->id)
                                        <p class="mt-1 text-xs font-medium text-amber-800">
                                            {{ __('Already in ":n" — must leave before accepting.', ['n' => $otherPb->name]) }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('communities.join-requests.accept', [$community, $jr]) }}">
                                        @csrf
                                        <button type="submit"
                                            @if($otherPb && $otherPb->id !== $community->id) disabled @endif
                                            class="inline-flex items-center justify-center rounded-md bg-red-900 px-3 py-2 text-xs font-semibold tracking-widest text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                            {{ __('Accept') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('communities.join-requests.ignore', [$community, $jr]) }}">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold tracking-widest text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                                            {{ __('Ignore') }}
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    @if (session('status') === 'join-request-accepted')
        <x-toast message="{{ __('Join request accepted.') }}" color="emerald" />
    @elseif (session('status') === 'join-request-ignored')
        <x-toast message="{{ __('Join request ignored.') }}" />
    @elseif ($errors->has('join_request'))
        <x-toast message="{{ $errors->first('join_request') }}" color="rose" />
    @endif
</x-app-layout>
