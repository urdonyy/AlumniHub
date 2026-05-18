<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $community->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="space-y-5 px-6 py-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-2xl font-semibold text-gray-900">{{ $community->name }}</h3>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ $community->description ?? __('No description provided yet.') }}
                            </p>
                        </div>

                        <div
                            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $user->communityAccessBadgeClass() }}">
                            {{ $user->communityAccessLabel() }}
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Members') }}
                            </p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $community->members_count }}</p>
                        </div>

                        <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('Created by') }}
                            </p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">
                                {{ $community->creator?->name ?? __('System') }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('Membership') }}
                            </p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">
                                {{ $isMember ? __('Joined') : __('Not joined') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($community->rules as $rule)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                {{ $rule->batch_year ? 'Batch ' . $rule->batch_year : __('Any batch') }}
                                ·
                                {{ $rule->program_course ?? __('Any program') }}
                            </span>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-5">
                        <a href="{{ route('communities.posts.index', $community) }}"
                            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                            📝 {{ __('View Posts') }}
                        </a>

                        @if ($canInteract)
                            @if ($isMember)
                                <form method="post" action="{{ route('communities.leave', $community) }}">
                                    @csrf
                                    @method('delete')
                                    <x-primary-button>{{ __('Leave Community') }}</x-primary-button>
                                </form>
                            @else
                                <form method="post" action="{{ route('communities.join', $community) }}">
                                    @csrf
                                    <x-primary-button>{{ __('Join Community') }}</x-primary-button>
                                </form>
                            @endif
                        @else
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                {{ __('This community is read-only until your account is verified. You can browse it, but posting and membership actions are disabled.') }}
                            </div>
                        @endif

                        <a href="{{ route('communities.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            {{ __('Back to communities') }}
                        </a>

                        @if ($user->canManageCommunities())
                            <a href="{{ route('admin.communities.index') }}"
                                class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-600">
                                {{ __('Manage Communities') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="px-6 py-5">
                    <h4 class="text-lg font-semibold text-gray-900">{{ __('Members') }}</h4>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Browse member profiles. Unverified viewers will only see limited details.') }}
                    </p>
                </div>

                <div class="border-t border-gray-200 px-6 py-5 text-sm text-gray-600 space-y-3">
                    <p>{{ __('Member count: :count', ['count' => $community->members_count]) }}</p>

                    @if ($community->members->isNotEmpty())
                        <ul class="grid gap-2 sm:grid-cols-2">
                            @foreach ($community->members as $member)
                                <li>
                                    <a href="{{ route('profiles.show', $member) }}"
                                        class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        {{ $member->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>