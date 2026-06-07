@props(['message', 'color' => 'gray'])

@php
    $colorClasses = match($color) {
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'rose'    => 'border-rose-200 bg-rose-50 text-rose-800',
        'blue'    => 'border-blue-200 bg-blue-50 text-blue-800',
        'indigo'  => 'border-indigo-200 bg-indigo-50 text-indigo-800',
        default   => 'border-gray-200 bg-white text-gray-700',
    };
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 5000)"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed bottom-4 left-4 z-50 w-[calc(100vw-2rem)] max-w-sm sm:bottom-6 sm:left-6 sm:w-auto"
    style="display:none;">
    <div class="flex items-start gap-3 rounded-xl border px-4 py-3 text-sm shadow-lg {{ $colorClasses }}">
        <span class="flex-1">{{ $message }}</span>
        <button type="button" @click="show = false" class="mt-px shrink-0 opacity-50 transition hover:opacity-100">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
