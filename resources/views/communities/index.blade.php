<x-app-layout>
    <x-slot name="title">Communities</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
            {{ __('Communities') }}
        </h2>
        <p class="text-sm text-red-900">{{ __('Discover and join alumni communities') }}</p>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div
                    class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Community Directory') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Browse assigned communities before verification and join the rest once your account is approved.') }}
                        </p>
                    </div>

                    <div
                        class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $user->communityAccessBadgeClass() }}">
                        {{ $user->communityAccessLabel() }}
                    </div>
                </div>

                @if ($user->canManageCommunities())
                    <div class="border-b border-gray-200 bg-indigo-50 px-6 py-4 flex flex-wrap gap-2">
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

                @if ($user->isVerified() && ! $user->canManageCommunities())
                    @if ($activeCreationRequest ?? null)
                        <div class="border-b border-gray-200 bg-amber-50 px-6 py-4 flex items-center justify-between gap-3">
                            <p class="text-sm text-amber-900">
                                {{ __('You have a pending community request: ":n" — :s.', [
                                    'n' => $activeCreationRequest->name,
                                    's' => str_replace('_', ' ', $activeCreationRequest->status),
                                ]) }}
                            </p>
                            <a href="{{ route('communities.requests.show', $activeCreationRequest) }}"
                                class="inline-flex items-center rounded-md bg-amber-700 px-4 py-2 text-xs font-semibold tracking-widest text-white transition hover:bg-amber-800">
                                {{ __('View your request') }}
                            </a>
                        </div>
                    @else
                        <div class="border-b border-gray-200 bg-emerald-50 px-6 py-4 flex items-center justify-between gap-3">
                            <p class="text-sm text-emerald-900">
                                {{ __("Don't see your batch's community? Request one — your two co-moderator picks will be notified, then admins review.") }}
                            </p>
                            <a href="{{ route('communities.requests.create') }}"
                                class="inline-flex items-center rounded-md bg-red-900 px-4 py-2 text-xs font-semibold tracking-widest text-white transition hover:bg-red-800">
                                {{ __('Request a community') }}
                            </a>
                        </div>
                    @endif
                @endif

                <div class="divide-y divide-gray-200">
                    @forelse ($communities as $community)
                        <div class="flex flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-base font-semibold text-gray-900">{{ $community->name }}</h4>
                                    @if ($community->isProgramBatch())
                                        <span
                                            class="rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-800">{{ __('Cohort') }}</span>
                                    @endif
                                    @if (in_array($community->id, $memberCommunityIds, true))
                                        <span
                                            class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">{{ __('Joined') }}</span>
                                    @endif
                                </div>

                                <p class="text-sm text-gray-600">
                                    {{ $community->description ?? __('No description provided yet.') }}
                                </p>

                                <div class="flex flex-wrap gap-2 text-xs font-medium text-gray-600">
                                    <span
                                        class="rounded-full bg-gray-100 px-2.5 py-1">{{ trans_choice('{0} No members yet.|{1} :count member|[2,*] :count members', $community->members_count, ['count' => $community->members_count]) }}</span>
                                    @foreach ($community->rules as $rule)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1">
                                            {{ $rule->batch_year ? 'Batch ' . $rule->batch_year : __('Any batch') }}
                                            ·
                                            {{ $rule->program_course ?? __('Any program') }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <a href="{{ route('communities.show', $community) }}"
                                class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                {{ __('View') }}
                            </a>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-gray-600">
                            {{ __('No communities are available yet.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>