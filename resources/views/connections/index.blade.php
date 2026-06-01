<x-app-layout>
    <x-slot name="title">Connections</x-slot>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                {{ __('Connections') }}
            </h2>
            <p class="text-sm text-red-900">{{ __('Manage your invites and accepted connections.') }}</p>
        </div>
    </x-slot>

    @php
        $isVerified = auth()->user()->isVerified();
        $initialPendingReceived = $pendingReceived->count();
        $initialPendingSent = $pendingSent->count();
        $initialAccepted = $acceptedConnections->count();

        $suggestedPayload = $isVerified
            ? $suggestedPeople->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'program' => $p->program_course,
                'batch' => $p->batch_year,
                'avatar' => $p->profileAvatarUrl(),
                'profile_url' => route('profiles.show', $p->id),
                'invite_url' => route('connections.invite', $p->id),
            ])->values()
            : collect();
    @endphp

    <div class="py-8"
        x-data="connectionsPage({
            countsUrl: '{{ route('connections.counts') }}',
            initial: {
                pending_received: {{ $initialPendingReceived }},
                pending_sent: {{ $initialPendingSent }},
                accepted: {{ $initialAccepted }},
            },
            suggested: @js($suggestedPayload),
        })"
        x-init="startPolling()">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-12">
                {{-- LEFT RAIL (sticky) --}}
                <aside class="min-w-0 space-y-3 lg:col-span-5 lg:sticky lg:top-6 lg:self-start">
                    {{-- Pending Invites Received --}}
                    <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                                {{ __('Pending Invites Received') }}
                            </h3>
                            <span x-show="counts.pending_received > 0"
                                x-text="counts.pending_received"
                                class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-200"></span>
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse ($pendingReceived as $invite)
                                <div class="flex flex-col gap-3 rounded-lg border border-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                    <a href="{{ route('profiles.show', $invite->sender) }}" class="flex items-center gap-3 min-w-0">
                                        <img src="{{ $invite->sender->profileAvatarUrl() }}"
                                            alt="{{ $invite->sender->name }}"
                                            class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover"
                                            onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-900">{{ $invite->sender->name }}</p>
                                            <p class="truncate text-xs text-gray-600">
                                                {{ $invite->sender->program_course ?? __('Program pending') }}
                                            </p>
                                        </div>
                                    </a>
                                    <div class="flex gap-2 shrink-0">
                                        <form method="POST" action="{{ route('connections.accept', $invite) }}">
                                            @csrf
                                            <x-tertiary-button>{{ __('Accept') }}</x-tertiary-button>
                                        </form>
                                        <form method="POST" action="{{ route('connections.ignore', $invite) }}">
                                            @csrf
                                            <x-ghost-button>{{ __('Ignore') }}</x-ghost-button>
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

                    {{-- Invites You Sent --}}
                    <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6 shadow-sm"
                        x-data="{ managing: false, selected: [] }">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Invites You Sent') }}
                                </h3>
                                <span x-show="counts.pending_sent > 0"
                                    x-text="counts.pending_sent"
                                    class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-800 ring-1 ring-inset ring-gray-200"></span>
                            </div>
                            @if ($pendingSent->isNotEmpty())
                                <button type="button"
                                    @click="managing = !managing; selected = []"
                                    class="text-xs font-semibold"
                                    :class="managing ? 'text-red-700 hover:text-red-900' : 'text-gray-500 hover:text-gray-700'">
                                    <span x-text="managing ? '{{ __('Cancel') }}' : '{{ __('Manage') }}'"></span>
                                </button>
                            @endif
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse ($pendingSent as $invite)
                                <div class="flex items-center gap-3 rounded-lg border px-4 py-3 transition"
                                    :class="managing && selected.includes({{ $invite->id }}) ? 'border-red-300 bg-red-50' : 'border-gray-200'">
                                    {{-- Checkbox (manage mode only) --}}
                                    <div x-show="managing" class="shrink-0">
                                        <input type="checkbox"
                                            :value="{{ $invite->id }}"
                                            x-model="selected"
                                            class="h-4 w-4 rounded border-gray-300 text-red-900     accent-red-900 cursor-pointer focus:ring-yellow-500">
                                    </div>
                                    {{-- Person info --}}
                                    <a href="{{ route('profiles.show', $invite->recipient) }}"
                                        class="flex flex-1 items-center gap-3 min-w-0">
                                        <img src="{{ $invite->recipient->profileAvatarUrl() }}"
                                            alt="{{ $invite->recipient->name }}"
                                            class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover"
                                            onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-900">{{ $invite->recipient->name }}</p>
                                            <p class="text-xs text-gray-600">{{ __('Invited') }}</p>
                                        </div>
                                    </a>
                                </div>
                            @empty
                                <p class="rounded-lg border border-dashed border-gray-300 px-3 py-3 text-sm text-gray-500">
                                    {{ __('You have no outgoing invites.') }}
                                </p>
                            @endforelse
                        </div>

                        {{-- Withdraw selected --}}
                        <div x-show="managing && selected.length > 0"
                            x-transition
                            class="mt-4 flex items-center justify-between gap-3 rounded-lg px-4 py-3">
                            <p class="text-sm text-red-800">
                                <span x-text="selected.length"></span> invite<span x-show="selected.length !== 1">s</span> selected
                            </p>
                            <button type="button"
                                @click="
                                    const csrf = document.querySelector('meta[name=csrf-token]')?.content;
                                    Promise.all(selected.map(id =>
                                        fetch(`/connections/${id}/withdraw`, {
                                            method: 'DELETE',
                                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                                        })
                                    )).then(() => window.location.reload())
                                "
                                class="rounded-md bg-red-900 px-3 py-1.5 text-xs font-semibold tracking-widest text-white hover:bg-red-800">
                                {{ __('Withdraw') }}
                            </button>
                        </div>
                    </section>

                    {{-- Accepted Connections (live badge) --}}
                    <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                                {{ __('Accepted Connections') }}
                            </h3>
                            <span x-text="counts.accepted"
                                class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-200"></span>
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse ($acceptedConnections as $connection)
                                @php $person = $connection->otherPartyFor(auth()->user()); @endphp
                                @if ($person)
                                    <div class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3">
                                        <a href="{{ route('profiles.show', $person) }}"
                                            class="flex flex-1 items-center gap-3 min-w-0 hover:opacity-80">
                                            <img src="{{ $person->profileAvatarUrl() }}"
                                                alt="{{ $person->name }}"
                                                class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover"
                                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-gray-900">{{ $person->name }}</p>
                                                <p class="truncate text-xs text-gray-600">{{ $person->program_course ?? __('Program pending') }}</p>
                                            </div>
                                        </a>
                                        {{-- Three-dot menu --}}
                                        <div class="relative shrink-0" x-data="{ open: false }" @click.away="open = false">
                                            <button type="button" @click="open = !open"
                                                class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                                </svg>
                                            </button>
                                            <div x-show="open"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute right-0 top-9 z-20 w-44 rounded-xl border border-gray-200 bg-white shadow-lg"
                                                style="display:none;">
                                                <form method="POST" action="{{ route('connections.remove', $connection) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-full rounded-xl px-4 py-2.5 text-left text-sm font-medium text-red-700 hover:bg-red-50 transition">
                                                        {{ __('Remove connection') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <p class="rounded-lg border border-dashed border-gray-300 px-3 py-3 text-sm text-gray-500">
                                    {{ __('Accepted connections will appear here.') }}
                                </p>
                            @endforelse
                        </div>
                    </section>
                </aside>

                {{-- RIGHT COLUMN: suggested people (verified only) --}}
                <section class="min-w-0 lg:col-span-7">
                    @if ($isVerified)
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                            {{-- Sticky search header --}}
                            <div class="sticky top-0 z-10 rounded-t-2xl border-b border-gray-200 bg-white/95 px-4 py-3 sm:px-6 sm:py-4 backdrop-blur">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                                        {{ __('Suggested People') }}
                                    </h3>
                                    <span class="text-xs text-gray-500" x-text="filteredCount + ' result' + (filteredCount === 1 ? '' : 's')"></span>
                                </div>
                                <div class="relative mt-3">
                                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                                    </svg>
                                    <input type="text"
                                        x-model="query"
                                        placeholder="{{ __('Search by name, program, or batch year…') }}"
                                        class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-8 py-2.5 text-sm text-gray-800 focus:border-yellow-500 focus:outline-none focus:ring-1 focus:ring-red-50">
                                    <button
                                        x-show="query.trim()"
                                        @click="query = ''"
                                        type="button"
                                        aria-label="{{ __('Clear search') }}"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-red-300 transition hover:text-red-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Scrollable list --}}
                            <div class="px-4 py-4 sm:px-6 sm:py-5 space-y-3">
                                <template x-for="person in visiblePeople" :key="person.id">
                                    <div class="flex flex-col gap-3 rounded-lg border border-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                        <a :href="person.profile_url" class="flex items-center gap-3 min-w-0">
                                            <img :src="person.avatar" :alt="person.name"
                                                class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover"
                                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-gray-900" x-text="person.name"></p>
                                                <p class="truncate text-xs text-gray-600">
                                                    <span x-text="person.program || '{{ __('Program pending') }}'"></span>
                                                    <template x-if="person.batch">
                                                        <span> · Batch <span x-text="person.batch"></span></span>
                                                    </template>
                                                </p>
                                            </div>
                                        </a>
                                        <form method="POST" :action="person.invite_url" @submit="onInviteSubmit(person.id)" class="shrink-0">
                                            @csrf
                                            <button type="submit"
                                                :disabled="invitedIds.includes(person.id)"
                                                class="inline-flex items-center gap-1.5 rounded-md bg-red-900 px-3 py-2 text-xs font-semibold tracking-widest text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:bg-amber-200 disabled:text-amber-700">
                                                <template x-if="!invitedIds.includes(person.id)">
                                                    <span>{{ __('Connect') }}</span>
                                                </template>
                                                <template x-if="invitedIds.includes(person.id)">
                                                    <span>{{ __('Invited') }}</span>
                                                </template>
                                            </button>
                                        </form>
                                    </div>
                                </template>

                                <template x-if="filteredCount === 0">
                                    <p class="rounded-lg border border-dashed border-gray-300 px-3 py-6 text-center text-sm text-gray-500">
                                        <span x-show="query.trim().length === 0">{{ __('No suggestions right now. Check back later.') }}</span>
                                        <span x-show="query.trim().length > 0">{{ __('No matches for') }} "<span x-text="query"></span>".</span>
                                    </p>
                                </template>

                                <div class="pt-2 text-center" x-show="filteredCount > visibleLimit">
                                    <button type="button" @click="visibleLimit += 20"
                                        class="rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                                        {{ __('Show more') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-8 shadow-sm flex flex-col items-center justify-center gap-4 text-center min-h-[240px]">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-amber-900">{{ __('Verify your account to connect with people') }}</h3>
                                <p class="mt-1 text-sm text-amber-800">
                                    {{ __('Suggested people and the ability to send connection invites are available to verified alumni and students.') }}
                                </p>
                            </div>
                            @if (auth()->user()->hasPendingVerificationDocument())
                                <div class="relative group inline-block">
                                    <span class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg bg-amber-200 px-4 py-2 text-sm font-semibold text-amber-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ __('Pending review') }}
                                    </span>
                                    <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 px-2.5 py-1.5 text-xs text-white group-hover:block">
                                        {{ __('Waiting for admin approval') }}
                                    </div>
                                </div>
                            @else
                                <a href="{{ route('profile.edit', ['section' => 'verification-document']) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('Verify now') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>

    <script>
        function connectionsPage(config) {
            return {
                countsUrl: config.countsUrl,
                counts: {
                    pending_received: config.initial.pending_received,
                    pending_sent: config.initial.pending_sent,
                    accepted: config.initial.accepted,
                },
                suggested: config.suggested,
                query: '',
                visibleLimit: 20,
                invitedIds: [],
                pollHandle: null,

                startPolling() {
                    const tick = async () => {
                        try {
                            const r = await fetch(this.countsUrl, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            });
                            if (!r.ok) return;
                            const data = await r.json();
                            this.counts = {
                                pending_received: data.pending_received ?? this.counts.pending_received,
                                pending_sent: data.pending_sent ?? this.counts.pending_sent,
                                accepted: data.accepted ?? this.counts.accepted,
                            };
                        } catch (_) { /* silent */ }
                    };
                    this.pollHandle = setInterval(tick, 30000);
                },

                onInviteSubmit(personId) {
                    // optimistic UI: mark as invited so button flips even before redirect
                    if (!this.invitedIds.includes(personId)) {
                        this.invitedIds.push(personId);
                    }
                },

                fuzzyMatch(haystack, needle) {
                    if (!needle) return true;
                    const h = (haystack || '').toLowerCase();
                    const n = needle.toLowerCase();
                    if (h.includes(n)) return true;
                    // subsequence match (cheap "fuzzy")
                    let i = 0;
                    for (const ch of h) {
                        if (ch === n[i]) i++;
                        if (i === n.length) return true;
                    }
                    return false;
                },

                get filtered() {
                    const q = this.query.trim();
                    if (!q) return this.suggested;
                    return this.suggested.filter(p => {
                        const blob = [p.name, p.program, p.batch].filter(Boolean).join(' ');
                        return this.fuzzyMatch(blob, q);
                    });
                },

                get visiblePeople() {
                    return this.filtered.slice(0, this.visibleLimit);
                },

                get filteredCount() {
                    return this.filtered.length;
                },
            };
        }
    </script>
</x-app-layout>
