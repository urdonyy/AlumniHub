<x-app-layout>
    <x-slot name="title">Coming Soon</x-slot>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                {{ __($title) }}
            </h2>
            <span
                class="inline-flex w-fit items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-800 ring-1 ring-inset ring-amber-200">
                {{ __('Coming soon') }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-sm leading-6 text-gray-700">{{ __($description) }}</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Status') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ __('UI placeholder ready') }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Backend') }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ __('Pending implementation') }}</p>
                    </div>
                </div>

                <a href="{{ route('dashboard') }}"
                    class="mt-6 inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
                    {{ __('Back to Home') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>