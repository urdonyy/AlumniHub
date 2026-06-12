<x-app-layout>
    <x-slot name="title">Alumni Verifications</x-slot>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                {{ __('Verification Queue') }}
            </h2>
            <p class="text-sm text-red-900">{{ __('Review and approve alumni verification documents') }}</p>
        </div>
    </x-slot>

    <div class="pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Status flashes --}}
            @if (session('status') === 'verification-approved')
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ __('Verification approved successfully.') }}
                </div>
            @elseif (session('status') === 'verification-rejected')
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ __('Verification rejected successfully.') }}
                </div>
            @elseif (session('status') === 'verification-already-reviewed')
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    {{ __('This verification document was already reviewed.') }}
                </div>
            @endif

            {{-- Filter --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-gray-900">{{ __('Verification Documents') }}</h3>
                <form method="get" class="flex items-center gap-2">
                    <select id="status" name="status"
                        class="h-[38px] rounded-md border-gray-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                        <option value="pending" @selected($status === 'pending')>{{ __('Pending') }}</option>
                        <option value="approved" @selected($status === 'approved')>{{ __('Approved') }}</option>
                        <option value="rejected" @selected($status === 'rejected')>{{ __('Rejected') }}</option>
                    </select>
                    <button type="submit"
                        class="inline-flex h-[38px] items-center rounded-md bg-red-900 px-4 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                        {{ __('Filter') }}
                    </button>
                </form>
            </div>

            {{-- Document cards --}}
            <div class="space-y-4">
                @forelse ($documents as $document)
                    @php
                        $ext = strtolower(pathinfo($document->document_path, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png'], true);
                        $fileUrl = route('admin.verifications.document', $document);
                    @endphp
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md"
                        x-data="{ preview: false }">
                        {{-- Brand header strip --}}
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 bg-gradient-to-r from-red-900 to-red-800 px-5 py-4 text-white">
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-[#FFC107]">
                                    <i class="fa-solid fa-id-card"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold">{{ $document->user->name ?: __('(no name yet)') }}</p>
                                    <p class="truncate text-xs text-red-100/80">{{ $document->user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-red-100/80">{{ __('Submitted') }}</p>
                                    <p class="text-xs font-semibold">{{ $document->created_at->format('M d, Y · h:i A') }}</p>
                                </div>
                                @php
                                    $badge = [
                                        'pending' => 'bg-[#FFC107] text-red-900',
                                        'approved' => 'bg-emerald-400 text-emerald-950',
                                        'rejected' => 'bg-rose-300 text-rose-950',
                                    ][$document->status] ?? 'bg-white/20 text-white';
                                @endphp
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold uppercase {{ $badge }}">{{ $document->status }}</span>
                            </div>
                        </div>

                        <div class="p-5">
                            {{-- Preview toggle + inline (lazy) preview --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" @click="preview = !preview"
                                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                    <i class="fa-solid" :class="preview ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    <span x-text="preview ? '{{ __('Hide Preview') }}' : '{{ __('Preview') }}'"></span>
                                </button>
                                <a href="{{ $fileUrl }}?download=1"
                                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                    <i class="fa-solid fa-download text-[11px]"></i>{{ __('Download') }}
                                </a>
                            </div>

                            {{-- Loads only when expanded (egress-friendly) --}}
                            <div x-show="preview" x-cloak x-transition class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                <template x-if="preview">
                                    <div>
                                        @if ($isImage)
                                            <img src="{{ $fileUrl }}" alt="{{ __('Verification document') }}"
                                                class="mx-auto max-h-[600px] w-auto object-contain p-2" loading="lazy">
                                        @else
                                            <iframe src="{{ $fileUrl }}" class="h-[600px] w-full" title="{{ __('Verification document') }}"></iframe>
                                        @endif
                                    </div>
                                </template>
                            </div>

                            {{-- Existing review note (if any) --}}
                            @if ($document->admin_notes)
                                <div class="mt-4 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-600">
                                    <span class="font-semibold text-gray-700">{{ __('Note:') }}</span> {{ $document->admin_notes }}
                                </div>
                            @endif

                            {{-- Review actions --}}
                            @if ($document->status === 'pending')
                                <div class="mt-5 grid gap-4 border-t border-gray-100 pt-5 sm:grid-cols-2">
                                    <form method="post" action="{{ route('admin.verifications.approve', $document) }}" class="space-y-2">
                                        @csrf
                                        @method('patch')
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Approval note (optional)') }}</label>
                                        <textarea name="admin_notes" rows="2"
                                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                            placeholder="{{ __('Optional approval note') }}"></textarea>
                                        <button type="submit"
                                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                            <i class="fa-solid fa-check"></i>{{ __('Approve') }}
                                        </button>
                                    </form>

                                    <form method="post" action="{{ route('admin.verifications.reject', $document) }}" class="space-y-2">
                                        @csrf
                                        @method('patch')
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Rejection reason (required)') }}</label>
                                        <textarea name="admin_notes" rows="2"
                                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                                            placeholder="{{ __('Reason for rejection') }}" required></textarea>
                                        <button type="submit"
                                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-md bg-rose-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                                            <i class="fa-solid fa-xmark"></i>{{ __('Reject') }}
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-gray-400">
                                    <i class="fa-solid fa-circle-check"></i>{{ __('Reviewed') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
                        <i class="fa-solid fa-inbox text-3xl text-gray-300"></i>
                        <p class="mt-3 text-sm text-gray-500">{{ __('No verification documents found for this status.') }}</p>
                    </div>
                @endforelse
            </div>

            @if ($documents->hasPages())
                <div>{{ $documents->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
