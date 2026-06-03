@foreach ($members as $member)
    @php
        $programAbbr = null;
        if ($member->program_course && preg_match('/\(([^)]+)\)$/', $member->program_course, $m)) {
            $programAbbr = $m[1];
        }
        $meta = collect([
            $member->batch_year ? 'Batch ' . $member->batch_year : null,
            $programAbbr ?? $member->program_course,
        ])->filter()->implode(' · ');
    @endphp
    <a href="{{ route('profiles.show', $member) }}"
        class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 transition hover:border-gray-300 hover:bg-gray-50">
        <img src="{{ $member->profileAvatarUrl() }}" alt="{{ $member->name }}"
            class="h-9 w-9 shrink-0 rounded-full border border-gray-200 object-cover"
            onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-gray-900">{{ $member->name }}</p>
            @if ($meta)
                <p class="truncate text-xs text-gray-500">{{ $meta }}</p>
            @endif
        </div>
    </a>
@endforeach
