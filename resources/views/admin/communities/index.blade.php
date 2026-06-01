<x-app-layout>
    <x-slot name="title">Manage Communities</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Community Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (in_array(session('status'), ['community-created', 'community-updated', 'community-deleted', 'community-updated-system'], true))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    @if (session('status') === 'community-created')
                        {{ __('Community created.') }}
                    @elseif (session('status') === 'community-updated-system')
                        {{ __('System community updated. Slug remains protected.') }}
                    @elseif (session('status') === 'community-updated')
                        {{ __('Community updated.') }}
                    @else
                        {{ __('Community deleted.') }}
                    @endif
                </div>
            @endif

            @if (session('status') === 'community-delete-blocked-system')
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    {{ __('System communities are protected and cannot be deleted.') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Create Community') }}</h3>

                <form method="post" action="{{ route('admin.communities.store') }}"
                    class="mt-4 grid gap-4 sm:grid-cols-2">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="slug" :value="__('Slug')" />
                        <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" required />
                        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div>
                        <x-input-label for="rule_batch_year" :value="__('Rule: Batch Year (optional)')" />
                        <x-text-input id="rule_batch_year" name="rule_batch_year" type="number" min="2024"
                            max="{{ now()->format('Y') }}" class="mt-1 block w-full" />
                        <x-input-error class="mt-2" :messages="$errors->get('rule_batch_year')" />
                    </div>

                    <div>
                        <x-input-label for="rule_program_course" :value="__('Rule: Program/Course (optional)')" />
                        <x-text-input id="rule_program_course" name="rule_program_course" type="text"
                            class="mt-1 block w-full" />
                        <x-input-error class="mt-2" :messages="$errors->get('rule_program_course')" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-primary-button>{{ __('Create') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Community') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Rules') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Members') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($communities as $community)
                                <tr>
                                    <td class="px-4 py-3">
                                        <form method="post" action="{{ route('admin.communities.update', $community) }}"
                                            class="space-y-2">
                                            @csrf
                                            @method('patch')
                                            <div class="flex items-center gap-2">
                                                <x-text-input name="name" type="text" class="block w-full"
                                                    :value="$community->name" required />
                                                @if ($community->is_system)
                                                    <span
                                                        class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ __('System') }}</span>
                                                @endif
                                            </div>
                                            <x-text-input name="slug" type="text" class="block w-full"
                                                :value="$community->slug" required :disabled="$community->is_system" />
                                            <textarea name="description" rows="2"
                                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $community->description }}</textarea>

                                            <button type="submit"
                                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Save') }}</button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3">
                                        @forelse ($community->rules as $rule)
                                            <div
                                                class="mb-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700 inline-flex">
                                                {{ $rule->batch_year ? 'Batch ' . $rule->batch_year : __('Any batch') }} ·
                                                {{ $rule->program_course ?? __('Any program') }}
                                            </div>
                                        @empty
                                            <span class="text-gray-500">{{ __('No rules') }}</span>
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-3">{{ $community->members_count }}</td>
                                    <td class="px-4 py-3">
                                        @if ($community->is_system)
                                            <span
                                                class="text-xs font-semibold uppercase tracking-widest text-gray-500">{{ __('Protected') }}</span>
                                        @else
                                            <form method="post" action="{{ route('admin.communities.destroy', $community) }}"
                                                onsubmit="return confirm('Delete this community?')">
                                                @csrf
                                                @method('delete')
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-rose-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                        {{ __('No communities found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>