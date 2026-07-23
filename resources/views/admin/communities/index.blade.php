<x-app-layout>
    <x-slot name="title">Manage Communities</x-slot>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                {{ __('Community Management') }}
            </h2>
            <p class="text-sm text-red-900">{{ __('Manage seeded and program-batch communities') }}</p>
        </div>
    </x-slot>

    <div class="pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Status flashes --}}
            @if (in_array(session('status'), ['community-created', 'community-updated', 'community-deleted', 'community-updated-system'], true))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
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
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    {{ __('System communities are protected and cannot be deleted.') }}
                </div>
            @endif

            {{-- Page intro --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-gray-900">{{ __('Communities') }}</h3>
                <span class="inline-flex items-center gap-2 rounded-full bg-red-900/10 px-3 py-1 text-sm font-semibold text-red-900">
                    <i class="fa-solid fa-people-group"></i>
                    {{ $communities->count() }} {{ Str::plural('community', $communities->count()) }}
                </span>
            </div>

            {{--
                admin "Create Community" form hidden intentionally. kept in code for reference purposes only.

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
            --}}

            {{-- Community cards --}}
            <div class="space-y-4">
                @forelse ($communities as $community)
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
                        {{-- Brand meta strip: identity + members + rules --}}
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-3 bg-gradient-to-r from-red-900 to-red-800 px-5 py-4 text-white">
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-[#FFC107]">
                                    <i class="fa-solid fa-users"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold">{{ $community->name }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        @if ($community->is_system)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-[#FFC107] px-2 py-0.5 text-[11px] font-semibold text-red-900">
                                                <i class="fa-solid fa-lock text-[9px]"></i>{{ __('System') }}
                                            </span>
                                        @endif
                                        @forelse ($community->rules as $rule)
                                            <span class="inline-flex items-center rounded-full bg-white/15 px-2 py-0.5 text-[11px] text-red-50">
                                                {{ $rule->batch_year ? 'Batch ' . $rule->batch_year : __('Any batch') }} ·
                                                {{ $rule->program_course ?? __('Any program') }}
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-red-100/70">{{ __('No rules') }}</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-red-100/80">{{ __('Members') }}</p>
                                <p class="text-xl font-bold leading-none">{{ number_format($community->members_count) }}</p>
                            </div>
                        </div>

                        {{-- Editable details --}}
                        <div class="p-5">
                            <form method="post" action="{{ route('admin.communities.update', $community) }}"
                                class="space-y-3">
                                @csrf
                                @method('patch')

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Name') }}</label>
                                        <x-text-input name="name" type="text" class="mt-1 block w-full"
                                            :value="$community->name" required />
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            {{ __('Slug') }}
                                            @if ($community->is_system)
                                                <i class="fa-solid fa-lock text-[10px] text-gray-400"></i>
                                            @endif
                                        </label>
                                        @if ($community->is_system)
                                            <div class="relative mt-1">
                                                <input type="text" value="{{ $community->slug }}" disabled readonly
                                                    class="block w-full cursor-not-allowed rounded-md border-gray-200 bg-gray-100 pr-9 text-gray-500 shadow-sm">
                                                <i class="fa-solid fa-lock pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-400">{{ __('Slug is protected for system communities.') }}</p>
                                        @else
                                            <x-text-input name="slug" type="text" class="mt-1 block w-full"
                                                :value="$community->slug" required />
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Description') }}</label>
                                    <textarea name="description" rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500">{{ $community->description }}</textarea>
                                </div>

                                <div class="flex items-center justify-between gap-3 pt-1">
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-md bg-red-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                        <i class="fa-solid fa-floppy-disk text-[11px]"></i>{{ __('Save Changes') }}
                                    </button>

                                    @if ($community->is_system)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-gray-400">
                                            <i class="fa-solid fa-shield-halved"></i>{{ __('Protected') }}
                                        </span>
                                    @endif
                                </div>
                            </form>

                            @unless ($community->is_system)
                                <form method="post" action="{{ route('admin.communities.destroy', $community) }}"
                                    onsubmit="return confirm('Delete this community?')"
                                    class="mt-3 border-t border-gray-100 pt-3">
                                    @csrf
                                    @method('delete')
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-rose-200 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-rose-600 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2">
                                        <i class="fa-solid fa-trash-can text-[11px]"></i>{{ __('Delete community') }}
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
                        <i class="fa-solid fa-people-group text-3xl text-gray-300"></i>
                        <p class="mt-3 text-sm text-gray-500">{{ __('No communities found.') }}</p>
                    </div>
                @endforelse
            </div>

            {{-- Community creation requests entry point --}}
            <a href="{{ route('admin.community-requests.index') }}"
                class="group flex items-center justify-between gap-4 rounded-2xl border border-gray-200 bg-white px-6 py-5 shadow-sm transition hover:border-red-900/30 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-900/10 text-red-900">
                        <i class="fa-solid fa-clipboard-check text-lg"></i>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ __('Community Creation Requests') }}</p>
                        <p class="mt-0.5 text-sm text-gray-500">{{ __('Review batch-community requests from alumni awaiting your decision.') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if (($pendingRequestCount ?? 0) > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#FFC107] px-3 py-1 text-sm font-bold text-red-900">
                            {{ $pendingRequestCount }} {{ __('pending') }}
                        </span>
                    @else
                        <span class="text-xs font-medium text-gray-400">{{ __('None pending') }}</span>
                    @endif
                    <i class="fa-solid fa-chevron-right text-gray-300 transition group-hover:text-red-900"></i>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
