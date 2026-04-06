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
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Account Status') }}</p>
                        <div
                            class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ auth()->user()->accountStatusBadgeClass() }}">
                            {{ auth()->user()->accountStatusLabel() }}
                        </div>
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