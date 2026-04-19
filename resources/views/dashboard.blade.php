<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight">
                {{ auth()->user()->canManageCommunities() ? __('Admin Home') : __('Home') }}
            </h2>
            <p class="text-sm text-red-900">{{ __('AlumniHub social experience (beta)') }}</p>
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (auth()->user()->canManageCommunities())
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-gray-500">{{ __('Community Access') }}</p>
                        <div
                            class="mt-3 inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ auth()->user()->communityAccessBadgeClass() }}">
                            {{ auth()->user()->communityAccessLabel() }}
                        </div>
                        <p class="mt-4 text-sm text-gray-600">
                            {{ __('Use this admin homepage to review verifications and manage community assignment rules.') }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-indigo-900">{{ __('Admin Shortcuts') }}</h3>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ route('admin.communities.index') }}"
                                class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-600">
                                {{ __('Manage Communities') }}
                            </a>
                            <a href="{{ route('admin.verifications.index') }}"
                                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
                                {{ __('Review Verifications') }}
                            </a>
                        </div>
                    </div>
                </div>
            @else
                @php
                    $feedCards = [
                        [
                            'author' => 'Career Services Team',
                            'meta' => 'Campus Update',
                            'content' => 'Sample post placeholder: internship matching and alumni mentorship updates will appear here once posting is enabled.',
                        ],
                        [
                            'author' => 'Community Spotlight',
                            'meta' => 'Batch Highlight',
                            'content' => 'Sample post placeholder: your batch highlights and community stories will show in this section.',
                        ],
                        [
                            'author' => 'AlumniHub',
                            'meta' => 'Product Note',
                            'content' => 'Sample post placeholder: reactions, comments, and sharing are part of the next backend phase.',
                        ],
                    ];
                @endphp

                <div class="grid gap-6 lg:grid-cols-12">
                    <aside class="space-y-6 lg:col-span-3">
                        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Profile') }}</p>
                            <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</h3>
                            <p class="text-sm text-gray-600">{{ auth()->user()->program_course ?? __('Program pending') }}</p>
                            <div
                                class="mt-3 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ auth()->user()->accountStatusBadgeClass() }}">
                                {{ auth()->user()->accountStatusLabel() }}
                            </div>
                            <a href="{{ route('profiles.show', auth()->id()) }}"
                                class="mt-4 inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
                                {{ __('Open Profile') }}
                            </a>
                        </section>

                        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Shortcuts') }}</p>
                            <div class="mt-3 space-y-2 text-sm">
                                <a class="block text-gray-700 hover:text-gray-900" href="{{ route('communities.index') }}">{{ __('My Communities') }}</a>
                                <a class="block text-gray-700 hover:text-gray-900" href="{{ route('connections.index') }}">{{ __('Connections') }}</a>
                                <a class="block text-gray-700 hover:text-gray-900" href="{{ route('saved.index') }}">{{ __('Saved') }}</a>
                                <a class="block text-gray-700 hover:text-gray-900" href="{{ route('profile.edit') }}">{{ __('Account Settings') }}</a>
                            </div>
                        </section>
                    </aside>

                    <section class="space-y-6 lg:col-span-6">
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <label for="compose" class="sr-only">{{ __('Create Post') }}</label>
                            <input id="compose" type="text"
                                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="{{ __('Share an update with your alumni network (coming soon)') }}" disabled>

                            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">{{ __('Photo') }}</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">{{ __('Video') }}</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">{{ __('Event') }}</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-600">{{ __('Article') }}</span>
                            </div>
                        </div>

                        @foreach ($feedCards as $card)
                            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">{{ $card['author'] }}</h3>
                                        <p class="text-xs uppercase tracking-wide text-gray-500">{{ $card['meta'] }}</p>
                                    </div>
                                    <span
                                        class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                        {{ __('Placeholder') }}
                                    </span>
                                </div>

                                <p class="mt-4 text-sm leading-6 text-gray-700">{{ $card['content'] }}</p>

                                <div class="mt-5 flex flex-wrap gap-2 text-xs">
                                    <button type="button" disabled
                                        class="rounded-md border border-gray-200 px-3 py-1 font-semibold text-gray-500">{{ __('Like') }}</button>
                                    <button type="button" disabled
                                        class="rounded-md border border-gray-200 px-3 py-1 font-semibold text-gray-500">{{ __('Comment') }}</button>
                                    <button type="button" disabled
                                        class="rounded-md border border-gray-200 px-3 py-1 font-semibold text-gray-500">{{ __('Share') }}</button>
                                </div>
                            </article>
                        @endforeach
                    </section>

                    <aside class="space-y-6 lg:col-span-3">
                        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Batch Communities') }}</h3>
                                <a href="{{ route('communities.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-700">
                                    {{ __('See all') }}
                                </a>
                            </div>

                            <div class="mt-4 space-y-3">
                                @forelse ($featuredCommunities as $community)
                                    <a href="{{ route('communities.show', $community) }}"
                                        class="block rounded-lg border border-gray-200 px-3 py-2 transition hover:border-gray-300 hover:bg-gray-50">
                                        <p class="text-sm font-semibold text-gray-900">{{ $community->name }}</p>
                                        <p class="text-xs text-gray-600">
                                            {{ trans_choice('{1} :count member|[2,*] :count members', $community->members_count, ['count' => $community->members_count]) }}
                                        </p>
                                    </a>
                                @empty
                                    <p class="rounded-lg border border-dashed border-gray-300 px-3 py-3 text-xs text-gray-500">
                                        {{ __('Community highlights will appear here once more communities are available.') }}
                                    </p>
                                @endforelse
                            </div>
                        </section>

                        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                                {{ __('Suggested People') }}</h3>
                            <div class="mt-4 space-y-3">
                                @forelse ($suggestedPeople as $person)
                                    <a href="{{ route('profiles.show', $person) }}"
                                        class="block rounded-lg border border-gray-200 px-3 py-2 transition hover:border-gray-300 hover:bg-gray-50">
                                        <p class="text-sm font-semibold text-gray-900">{{ $person->name }}</p>
                                        <p class="text-xs text-gray-600">{{ $person->program_course ?? __('Program pending') }}</p>
                                    </a>
                                @empty
                                    <p class="rounded-lg border border-dashed border-gray-300 px-3 py-3 text-xs text-gray-500">
                                        {{ __('People suggestions will be populated after connection features are enabled.') }}
                                    </p>
                                @endforelse
                            </div>
                        </section>
                    </aside>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>