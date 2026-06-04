<x-app-layout>
    <x-slot name="title">{{ __('Manage Members') }} · {{ $community->name }}</x-slot>
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
            <span class="font-semibold text-red-900">{{ __('Manage Members') }}</span>
        </nav>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-5">
                    <h4 class="text-lg font-semibold text-gray-900">{{ __('Manage members') }}</h4>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Remove a member from this community. Other moderators cannot be removed.') }}</p>
                </div>
                @if ($community->members->isEmpty())
                    <p class="px-6 py-6 text-center text-sm text-gray-500">{{ __('No members yet.') }}</p>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach ($community->members as $member)
                            @if ($member->id === $user->id)
                                @continue
                            @endif
                            <li class="flex items-center justify-between px-6 py-3">
                                <a href="{{ route('profiles.show', $member) }}" class="text-sm font-medium text-gray-900 hover:underline">
                                    {{ $member->name }}
                                </a>
                                @if ($community->isModerator($member) && ! $isAdmin)
                                    <span class="text-xs font-semibold uppercase tracking-wide text-indigo-700">{{ __('Moderator') }}</span>
                                @else
                                    <div x-data="{
                                            open: false,
                                            above: false,
                                            toggle() {
                                                const rect = this.$el.getBoundingClientRect();
                                                this.above = rect.bottom + 100 > window.innerHeight;
                                                this.open = !this.open;
                                            }
                                        }" class="relative">
                                        <button type="button" @click="toggle()" @keydown.escape.window="open = false"
                                            class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 1 1 0-4 2 2 0 0 1 0 4ZM10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4ZM10 18a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z"/>
                                            </svg>
                                        </button>
                                        <div x-show="open" @click.outside="open = false" x-transition
                                            :class="above ? 'bottom-full mb-1' : 'top-full mt-1'"
                                            class="absolute right-0 z-20 w-44 rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
                                            @if ($isModerator && ! $isAdmin)
                                                @if (isset($myPendingTransfers[$member->id]))
                                                    @php($pendingTransfer = $myPendingTransfers[$member->id])
                                                    <div class="flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-indigo-600">
                                                        <svg class="h-3.5 w-3.5 shrink-0 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/>
                                                        </svg>
                                                        {{ __('Transfer pending…') }}
                                                    </div>
                                                    <form method="POST" action="{{ route('mod-transfers.cancel', $pendingTransfer) }}">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-gray-600 hover:bg-gray-50">
                                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                                            </svg>
                                                            {{ __('Cancel Invite') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('communities.mod-transfer.store', [$community, $member]) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-indigo-700 hover:bg-indigo-50">
                                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                                            </svg>
                                                            {{ __('Transfer Role') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                            <form method="POST" action="{{ route('communities.members.remove', [$community, $member]) }}">
                                                @csrf
                                                @method('delete')
                                                <button type="submit"
                                                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-rose-700 hover:bg-rose-50">
                                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>
                                                    </svg>
                                                    {{ __('Remove') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    @if (session('status') === 'member-removed')
        <x-toast message="{{ __('Member removed.') }}" color="emerald" />
    @elseif (session('status') === 'transfer-invite-sent')
        <x-toast message="{{ __('Transfer invite sent.') }}" color="indigo" />
    @elseif (session('status') === 'transfer-cancelled')
        <x-toast message="{{ __('Transfer invite cancelled.') }}" />
    @elseif ($errors->has('member'))
        <x-toast message="{{ $errors->first('member') }}" color="rose" />
    @endif
</x-app-layout>
