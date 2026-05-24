<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                {{ __('Connections') }}
            </h2>
            <p class="text-sm text-red-900">{{ __('Manage your invites and accepted connections.') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                    {{ __('Pending Invites Received') }}</h3>

                <div class="mt-4 space-y-3">
                    @forelse ($pendingReceived as $invite)
                        <div
                            class="flex flex-col gap-3 rounded-lg border border-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $invite->sender->name }}</p>
                                <p class="text-xs text-gray-600">
                                    {{ $invite->sender->program_course ?? __('Program pending') }}</p>
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('connections.accept', $invite) }}">
                                    @csrf
                                    <button type="submit"
                                        class="rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-emerald-500">
                                        {{ __('Accept') }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('connections.ignore', $invite) }}">
                                    @csrf
                                    <button type="submit"
                                        class="rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                                        {{ __('Ignore') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 px-3 py-3 text-sm text-gray-500">
                            {{ __('No pending invites right now.') }}
                        </p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">{{ __('Invites You Sent') }}
                </h3>

                <div class="mt-4 space-y-3">
                    @forelse ($pendingSent as $invite)
                        <div class="rounded-lg border border-gray-200 px-4 py-3">
                            <p class="text-sm font-semibold text-gray-900">{{ $invite->recipient->name }}</p>
                            <p class="text-xs text-gray-600">{{ __('Invited') }}</p>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 px-3 py-3 text-sm text-gray-500">
                            {{ __('You have no outgoing invites.') }}
                        </p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">{{ __('Accepted Connections') }}
                </h3>

                <div class="mt-4 space-y-3">
                    @forelse ($acceptedConnections as $connection)
                        @php
                            $person = $connection->otherPartyFor(auth()->user());
                        @endphp
                        @if ($person)
                            <div class="rounded-lg border border-gray-200 px-4 py-3">
                                <p class="text-sm font-semibold text-gray-900">{{ $person->name }}</p>
                                <p class="text-xs text-gray-600">{{ $person->program_course ?? __('Program pending') }}</p>
                            </div>
                        @endif
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 px-3 py-3 text-sm text-gray-500">
                            {{ __('Accepted connections will appear here.') }}
                        </p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>