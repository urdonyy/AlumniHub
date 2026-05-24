<div class="space-y-2">
    <div class="grid gap-1.5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($flairs as $flair)
            <label
                class="flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="flairs[]" value="{{ $flair->id }}" class="h-3.5 w-3.5 rounded border-gray-300"
                    @if (in_array($flair->id, $selected ?? [])) checked @endif />
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        @if ($flair->icon)
                            <span class="text-xs leading-none">{{ $flair->icon }}</span>
                        @endif
                        <span class="text-xs font-medium text-gray-900">{{ $flair->name }}</span>
                    </div>
                    @if ($flair->color)
                        <div class="mt-0.5 inline-block h-1.5 w-1.5 rounded-full"
                            style="background-color: {{ $flair->color }};"></div>
                    @endif
                </div>
            </label>
        @empty
            <p class="text-xs text-gray-500">No flairs available</p>
        @endforelse
    </div>
</div>