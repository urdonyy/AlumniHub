<x-app-layout>
    <x-slot name="title">Reported Posts</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Reported Posts') }}</h2>
        <p class="text-sm text-gray-600">{{ __('Posts flagged by the community. Keep the post or remove it with a reason.') }}</p>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @forelse ($flaggedPosts as $post)
                @php
                    $reasonCounts = $post->reports->groupBy('reason')->map->count();
                    $bodyPreview = \Illuminate\Support\Str::limit(strip_tags($post->body_html ?? $post->body_markdown), 280);
                @endphp
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm"
                    x-data="{ confirming: false, reason: '', note: '', viewing: false }">

                    {{-- Post header --}}
                    <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-6 py-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $post->user->name }}
                                @if ($post->community)
                                    <span class="text-gray-400">·</span>
                                    <span class="font-normal text-gray-500">{{ $post->community->name }}</span>
                                @endif
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500">
                                {{ __('Flagged') }} {{ $post->flagged_at?->diffForHumans() }}
                                · {{ $post->published_at?->diffForHumans() }}
                            </p>
                        </div>
                        <span class="inline-flex shrink-0 items-center rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800 ring-1 ring-inset ring-rose-200">
                            {{ $post->reports_count }} {{ __('reports') }}
                        </span>
                    </div>

                    {{-- Post content preview --}}
                    <div class="px-6 py-4">
                        @if ($post->title)
                            <p class="text-sm font-semibold text-gray-900">{{ $post->title }}</p>
                        @endif
                        @if ($bodyPreview)
                            <p class="mt-1 whitespace-pre-wrap text-sm text-gray-700">{{ $bodyPreview }}</p>
                        @endif

                        <button type="button" @click="viewing = true"
                            class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-indigo-700 hover:underline">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{ __('View post') }}
                        </button>

                        {{-- Reason breakdown --}}
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($reasonCounts as $key => $count)
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                    {{ \App\Models\PostReport::reasonLabel($key) }}
                                    <span class="text-gray-400">×{{ $count }}</span>
                                </span>
                            @endforeach
                        </div>

                        {{-- Reporter details (notes) --}}
                        @php $notes = $post->reports->filter(fn ($r) => filled($r->details)); @endphp
                        @if ($notes->isNotEmpty())
                            <div class="mt-3 space-y-1.5 rounded-lg bg-gray-50 px-3 py-2">
                                @foreach ($notes as $report)
                                    <p class="text-xs text-gray-600">
                                        <span class="font-semibold text-gray-700">{{ $report->user->name }}</span>
                                        <span class="text-gray-400">({{ \App\Models\PostReport::reasonLabel($report->reason) }}):</span>
                                        {{ $report->details }}
                                    </p>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{-- Default: Keep / Remove --}}
                        <div x-show="!confirming" class="flex justify-end gap-2">
                            <form method="POST" action="{{ route('admin.reports.keep', $post) }}">
                                @csrf
                                <button type="submit"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
                                    {{ __('Keep post') }}
                                </button>
                            </form>
                            <button type="button" @click="confirming = true"
                                class="rounded-lg bg-rose-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-600">
                                {{ __('Remove post') }}
                            </button>
                        </div>

                        {{-- Remove confirmation: pick reason + optional note --}}
                        <form x-show="confirming" x-cloak method="POST" action="{{ route('admin.reports.destroy', $post) }}"
                            @submit="if (!reason) { $event.preventDefault(); }">
                            @csrf
                            @method('DELETE')
                            <p class="text-sm font-medium text-gray-700">{{ __('Reason for removal') }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ __('The author is notified with this reason and a suspension warning.') }}</p>
                            <select name="reason" x-model="reason" required
                                class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-700 focus:ring-1 focus:ring-rose-700">
                                <option value="">{{ __('Select a reason...') }}</option>
                                @foreach ($reasons as $key => $label)
                                    <option value="{{ $key }}">{{ __($label) }}</option>
                                @endforeach
                            </select>
                            <textarea name="note" x-model="note" rows="2" maxlength="500"
                                placeholder="{{ __('Optional note to the author...') }}"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-rose-700 focus:ring-1 focus:ring-rose-700"></textarea>
                            <div class="mt-3 flex justify-end gap-2">
                                <button type="button" @click="confirming = false"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
                                    {{ __('Cancel') }}
                                </button>
                                <button type="submit" :disabled="!reason"
                                    class="rounded-lg bg-rose-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-600 disabled:bg-gray-300">
                                    {{ __('Remove') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- View post modal: full context for the admin (bypasses the
                         member-only view gate that 403s on the public open route) --}}
                    <div x-show="viewing" x-cloak
                        @keydown.escape.window="viewing = false"
                        class="fixed inset-0 z-[500] flex items-center justify-center p-4"
                        style="display:none">
                        <div class="fixed inset-0 bg-black/50" @click="viewing = false"></div>
                        <div class="relative flex max-h-[88vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
                            {{-- Modal header --}}
                            <div class="flex items-start gap-3 border-b border-gray-100 px-5 py-4">
                                <img src="{{ $post->user->profileAvatarUrl() }}" alt="{{ $post->user->name }}"
                                    class="h-11 w-11 shrink-0 rounded-full border border-gray-200 object-cover"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-gray-900">{{ $post->user->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        @php
                                            $vAbbr = null;
                                            if ($post->user->program_course && preg_match('/\(([^)]+)\)$/', $post->user->program_course, $mm)) {
                                                $vAbbr = $mm[1];
                                            }
                                            $vMeta = collect([
                                                $post->user->batch_year ? 'Batch ' . $post->user->batch_year : null,
                                                $vAbbr ?? $post->user->program_course,
                                            ])->filter()->implode(' · ');
                                        @endphp
                                        {{ $vMeta }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-400">
                                        {{ $post->community?->name ?? __('Post') }}
                                        · {{ ucfirst($post->visibility) }}
                                        · {{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <button type="button" @click="viewing = false"
                                    class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500 text-lg leading-none hover:bg-gray-200 transition"
                                    aria-label="Close">×</button>
                            </div>

                            {{-- Modal body --}}
                            <div class="flex-1 overflow-y-auto px-5 py-4">
                                @if ($post->flairs->count() > 0)
                                    <div class="mb-3 flex flex-wrap gap-1.5">
                                        @foreach ($post->flairs as $flair)
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                style="background-color: {{ $flair->color ? $flair->color . '20' : '#f3f4f6' }}; color: {{ $flair->color ?? '#374151' }}; border: 1px solid {{ $flair->color ?? '#e5e7eb' }};">
                                                @if ($flair->icon)<span>{{ $flair->icon }}</span>@endif
                                                {{ $flair->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($post->title)
                                    <h4 class="text-base font-semibold text-gray-900">{{ $post->title }}</h4>
                                @endif

                                @if ($post->event)
                                    <div class="mt-2 rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs text-indigo-800">
                                        <p class="font-semibold">{{ ucfirst($post->event->event_type) }} {{ __('event') }}</p>
                                        @if ($post->event->starts_at)
                                            <p>{{ $post->event->starts_at->format('M j, Y · g:i A') }}@if ($post->event->ends_at) – {{ $post->event->ends_at->format('M j, Y · g:i A') }}@endif</p>
                                        @endif
                                        @if ($post->event->venue){{ $post->event->venue }}@endif
                                        @if ($post->event->address)<p>{{ $post->event->address }}</p>@endif
                                        @if ($post->event->external_link)<p class="truncate">{{ $post->event->external_link }}</p>@endif
                                    </div>
                                @endif

                                @if ($post->body_html || $post->body_markdown)
                                    <div class="prose prose-sm mt-2 max-w-none break-words text-gray-800">
                                        {!! $post->body_html ?: e($post->body_markdown) !!}
                                    </div>
                                @endif

                                @if ($post->media->count() > 0)
                                    <div class="mt-3 space-y-2">
                                        @foreach ($post->media as $media)
                                            <img src="{{ $media->url }}" alt="{{ $media->alt_text ?? 'Post image' }}"
                                                class="w-full rounded-xl border border-gray-200 object-contain">
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-gray-200 bg-white px-6 py-16 text-center shadow-sm">
                    <i class="fa-solid fa-flag mb-3 text-3xl text-gray-300"></i>
                    <p class="text-sm font-medium text-gray-700">{{ __('No reported posts pending review.') }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Posts appear here once they reach') }} {{ \App\Models\PostReport::THRESHOLD }} {{ __('reports.') }}</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
