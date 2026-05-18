<div class="space-y-3">
    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($flairs as $flair)
            <label class="flex items-center gap-3 rounded-lg border border-gray-300 px-4 py-3 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="flairs[]" value="{{ $flair->id }}"
                    class="rounded border-gray-300"
                    @if (in_array($flair->id, $selected ?? [])) checked @endif />
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        @if ($flair->icon)
                            <span>{{ $flair->icon }}</span>
                        @endif
                        <span class="text-sm font-medium text-gray-900">{{ $flair->name }}</span>
                    </div>
                    @if ($flair->color)
                        <div class="mt-1 inline-block h-2 w-2 rounded-full"
                            style="background-color: {{ $flair->color }};"></div>
                    @endif
                </div>
            </label>
        @empty
            <p class="text-sm text-gray-500">No flairs available</p>
        @endforelse
    </div>
</div>
