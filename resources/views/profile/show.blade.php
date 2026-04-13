<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-2xl font-semibold text-gray-900">{{ $profileUser->name }}</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Account status: :status', ['status' => $profileUser->accountStatusLabel()]) }}
                            </p>
                        </div>

                        <div
                            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $viewer->profileVisibilityBadgeClass() }}">
                            {{ $viewer->profileVisibilityLabel() }}
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('Batch Year') }}</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ $profileUser->batch_year ?? __('Not provided') }}</p>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('Program / Course') }}</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ $profileUser->program_course ?? __('Not provided') }}</p>
                        </div>
                    </div>

                    @if ($showFullDetails)
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                                {{ __('Full Contact Details') }}</p>
                            <p class="mt-1 text-sm font-medium text-emerald-900">
                                {{ __('Email: :email', ['email' => $profileUser->email]) }}</p>
                        </div>
                    @else
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                            {{ __('Additional profile details are hidden until your account is verified.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>