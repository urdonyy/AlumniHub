<x-app-layout>
    <x-slot name="title">Communities</x-slot>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                {{ __('Communities') }}
            </h2>
            <p class="text-sm text-red-900">{{ __('Discover and join alumni communities') }}</p>
        </div>
    </x-slot>

    <div class="pb-24">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">

            {{-- Access badge --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-gray-600">
                    @if ($user->isInstitution())
                        {{ __('You moderate the General Hub and all program communities, and can request to join any batch community.') }}
                    @elseif ($user->canInteractInCommunities())
                        {{ __('You\'re auto-joined to the General Hub and your program community, and can request your batch\'s community.') }}
                    @else
                        {{ __('Browse your assigned communities now. Verify your account to interact and to request your batch\'s community.') }}
                    @endif
                </p>
                <span class="inline-flex shrink-0 items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $user->communityAccessBadgeClass() }}">
                    {{ $user->communityAccessLabel() }}
                </span>
            </div>

            {{-- Admin actions --}}
            @if ($user->canManageCommunities())
                <div class="flex flex-wrap gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-6 py-4">
                    <a href="{{ route('admin.communities.index') }}"
                        class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-600">
                        {{ __('Manage Communities') }}
                    </a>
                    <a href="{{ route('admin.community-requests.index') }}"
                        class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-600">
                        {{ __('Review Community Requests') }}
                    </a>
                </div>
            @endif

            {{-- Request CTA --}}
            @if ($user->isVerified() && ! $user->canManageCommunities() && ! $user->isInstitution())
                @if ($activeCreationRequest ?? null)
                    <div class="flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-amber-900">
                            {{ __('You have a pending community request: ":n" — :s.', [
                                'n' => $activeCreationRequest->name,
                                's' => str_replace('_', ' ', $activeCreationRequest->status),
                            ]) }}
                        </p>
                        <a href="{{ route('communities.requests.show', $activeCreationRequest) }}"
                            class="inline-flex self-end shrink-0 items-center rounded-md bg-amber-700 px-4 py-2 text-xs font-semibold tracking-widest text-white transition hover:bg-amber-800">
                            {{ __('View your request') }}
                        </a>
                    </div>
                @else
                    <div class="flex flex-col gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-emerald-900">
                            {{ __("Don't see your batch's community? Request one — your two co-moderator picks will be notified, then admins review.") }}
                        </p>
                        <a href="{{ route('communities.requests.create') }}"
                            class="inline-flex self-end shrink-0 items-center rounded-md bg-red-900 px-4 py-2 text-xs font-semibold tracking-widest text-white transition hover:bg-red-800">
                            {{ __('Request a community') }}
                        </a>
                    </div>
                @endif
            @endif

            {{-- 2-column grid --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- Left: System Community Directory --}}
                <div
                    x-data="{
                        search: '',
                        fuzzy(name) {
                            if (!this.search.trim()) return true;
                            return name.toLowerCase().includes(this.search.toLowerCase().trim());
                        },
                        get hasResults() {
                            return JSON.parse(this.$el.dataset.names).some(n => this.fuzzy(n));
                        }
                    }"
                    data-names="{{ json_encode($systemCommunities->pluck('name')->values()) }}"
                    class="flex flex-col rounded-2xl border border-gray-200 bg-white shadow-sm">

                    <div class="space-y-3 border-b border-gray-200 px-6 py-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Community Directory') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('System program communities for all alumni.') }}</p>
                        </div>
                        <div class="relative">
                            <input
                                x-model="search"
                                type="text"
                                placeholder="{{ __('Search communities…') }}"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-8 text-sm text-gray-800 placeholder-gray-400 focus:border-yellow-500 focus:outline-none focus:ring-1 focus:ring-red-50"
                            />
                            <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                            </svg>
                            <button
                                x-show="search.trim()"
                                @click="search = ''"
                                type="button"
                                aria-label="{{ __('Clear search') }}"
                                class="absolute right-2.5 top-2.5 text-red-300 transition hover:text-red-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 divide-y divide-gray-200">
                        @forelse ($systemCommunities as $community)
                            <div
                                x-show="fuzzy($el.dataset.name)"
                                x-transition
                                data-name="{{ $community->name }}"
                                class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0 space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ $community->name }}</h4>
                                        @if ($community->system_key === 'general-alumni-hub')
                                            <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">{{ __('General') }}</span>
                                        @endif
                                        @if (in_array($community->id, $memberCommunityIds, true))
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">{{ __('Joined') }}</span>
                                        @endif
                                    </div>
                                    <p class="line-clamp-2 text-xs text-gray-500">
                                        {{ $community->description ?? __('No description provided yet.') }}
                                    </p>
                                    <div class="flex flex-wrap gap-1.5 text-xs font-medium text-gray-500">
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5">
                                            {{ trans_choice('{0} No members yet.|{1} :count member|[2,*] :count members', $community->members_count, ['count' => $community->members_count]) }}
                                        </span>
                                        @foreach ($community->rules as $rule)
                                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5">
                                                {{ $rule->batch_year ? 'Batch ' . $rule->batch_year : __('Any batch') }}
                                                @if ($rule->program_course)· {{ $rule->program_course }}@endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <a href="{{ route('communities.show', $community) }}"
                                    class="inline-flex shrink-0 items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                                    {{ __('View') }}
                                </a>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center text-sm text-gray-500">
                                {{ __('No communities are available yet.') }}
                            </div>
                        @endforelse

                        @if ($systemCommunities->isNotEmpty())
                            <div x-show="search.trim() && !hasResults" class="px-6 py-10 text-center text-sm text-gray-500">
                                {{ __('No communities match your search.') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right: Alumni-Requested Batch Communities --}}
                <div
                    x-data="{
                        search: '',
                        fuzzy(name) {
                            if (!this.search.trim()) return true;
                            return name.toLowerCase().includes(this.search.toLowerCase().trim());
                        },
                        get hasResults() {
                            return JSON.parse(this.$el.dataset.names).some(n => this.fuzzy(n));
                        }
                    }"
                    data-names="{{ json_encode($createdBatchCommunities->pluck('name')->values()) }}"
                    class="flex flex-col rounded-2xl border border-gray-200 bg-white shadow-sm {{ $createdBatchCommunities->isEmpty() ? 'self-start' : '' }}">

                    <div class="space-y-3 border-b border-gray-200 px-6 py-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Batch Communities') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Alumni-requested communities for specific batch cohorts.') }}</p>
                        </div>
                        <div class="relative">
                            <input
                                x-model="search"
                                type="text"
                                placeholder="{{ __('Search batches…') }}"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-8 text-sm text-gray-800 placeholder-gray-400 focus:border-yellow-500 focus:outline-none focus:ring-1 focus:ring-red-50"
                            />
                            <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                            </svg>
                            <button
                                x-show="search.trim()"
                                @click="search = ''"
                                type="button"
                                aria-label="{{ __('Clear search') }}"
                                class="absolute right-2.5 top-2.5 text-red-300 transition hover:text-red-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 divide-y divide-gray-200">
                        @forelse ($createdBatchCommunities as $community)
                            <div
                                x-show="fuzzy($el.dataset.name)"
                                x-transition
                                data-name="{{ $community->name }}"
                                class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0 space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ $community->name }}</h4>
                                        <span class="rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-800">{{ __('Cohort') }}</span>
                                        @if (in_array($community->id, $memberCommunityIds, true))
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">{{ __('Joined') }}</span>
                                        @endif
                                    </div>
                                    <p class="line-clamp-2 text-xs text-gray-500">
                                        {{ $community->description ?? __('No description provided yet.') }}
                                    </p>
                                    <div class="flex flex-wrap gap-1.5 text-xs font-medium text-gray-500">
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5">
                                            {{ trans_choice('{0} No members yet.|{1} :count member|[2,*] :count members', $community->members_count, ['count' => $community->members_count]) }}
                                        </span>
                                        @foreach ($community->rules as $rule)
                                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5">
                                                {{ $rule->batch_year ? 'Batch ' . $rule->batch_year : __('Any batch') }}
                                                @if ($rule->program_course)· {{ $rule->program_course }}@endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <a href="{{ route('communities.show', $community) }}"
                                    class="inline-flex shrink-0 items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                                    {{ __('View') }}
                                </a>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center text-sm text-gray-500">
                                <p>{{ __('No batch communities yet.') }}</p>
                                @if ($user->isVerified() && ! $user->canManageCommunities() && ! $user->isInstitution() && ! ($activeCreationRequest ?? null))
                                    <p class="mt-1">
                                        {{ __('Be the first to') }}
                                        <a href="{{ route('communities.requests.create') }}" class="font-semibold text-red-900 hover:underline">{{ __('request one') }}</a>.
                                    </p>
                                @endif
                            </div>
                        @endforelse

                        @if ($createdBatchCommunities->isNotEmpty())
                            <div x-show="search.trim() && !hasResults" class="px-6 py-10 text-center text-sm text-gray-500">
                                {{ __('No batches match your search.') }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
<x-footer />