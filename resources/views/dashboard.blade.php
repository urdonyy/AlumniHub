<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div
                        class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Community Access') }}</p>
                            <div
                                class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ auth()->user()->communityAccessBadgeClass() }}">
                                {{ auth()->user()->communityAccessLabel() }}
                            </div>
                        </div>

                        <a href="{{ route('communities.index') }}"
                            class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
                            {{ __('Browse Communities') }}
                        </a>
                    </div>

                    @if (auth()->user()->canManageCommunities())
                        <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <p class="font-medium">{{ __('Admin Shortcut: Manage communities and assignment rules.') }}
                                </p>
                                <a href="{{ route('admin.communities.index') }}"
                                    class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-600">
                                    {{ __('Open Community Management') }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Account Status') }}</p>
                        <div
                            class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ auth()->user()->accountStatusBadgeClass() }}">
                            {{ auth()->user()->accountStatusLabel() }}
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Assigned Communities') }}</p>
                        <p class="mt-2 text-sm text-gray-700">
                            {{ trans_choice('{0} You are not assigned to any communities yet.|{1} You are assigned to :count community.|[2,*] You are assigned to :count communities.', auth()->user()->communities()->count(), ['count' => auth()->user()->communities()->count()]) }}
                        </p>
                    </div>

                    <p class="text-sm text-gray-600">
                        {{ auth()->user()->isVerifiedAlumni() ? __('Your alumni verification is complete.') : __('You can still use the system while your alumni verification is pending review.') }}
                    </p>

                    <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
                        {{ __("You're logged in!") }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>