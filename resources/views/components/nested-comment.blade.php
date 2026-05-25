@props(['comment', 'depth' => 0])

@if ($depth < 2) <!-- Limit nesting to 2 levels deep (3 total levels: 0, 1, 2) -->
    <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
        <div class="flex gap-3">
            <div class="flex-1">
                <div class="flex items-baseline gap-2">
                    <p class="font-semibold text-gray-900 text-sm">
                        {{ $comment['user']['name'] ?? 'Anonymous' }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ $comment['created_at'] ?? 'Just now' }}
                    </p>
                </div>
                <p class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">
                    {{ $comment['body'] }}
                </p>

                <!-- Action buttons -->
                <div class="mt-2 flex gap-3 text-xs">
                    <button type="button" class="text-blue-600 hover:text-blue-700">
                        Reply
                    </button>
                    <button type="button" class="text-red-600 hover:text-red-700">
                        Delete
                    </button>
                </div>

                <!-- Sub-replies -->
                @if (!empty($comment['replies']) && count($comment['replies']) > 0)
                    <div class="mt-3 space-y-2 border-l-2 border-gray-300 pl-3">
                        @foreach ($comment['replies'] as $subReply)
                            <x-nested-comment :comment="$subReply" :depth="$depth + 1" />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif