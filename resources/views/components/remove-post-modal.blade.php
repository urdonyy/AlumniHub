{{--
    Shared "Remove post" modal — moderator/superadmin action. Opened from a post's
    three-dot menu (only shown to a moderator who is NOT the author) via:
        $dispatch('open-remove-modal', { removeUrl: '<posts.moderate-remove URL>' })
    Include once per page that renders the feed (alongside <x-report-post-modal />).
    Submits a native POST so the server removes the post and redirects back, refreshing
    the feed without it. The author is notified with the chosen reason + optional note.
--}}
<div x-data="removePostModal()"
    x-show="open"
    x-cloak
    @open-remove-modal.window="openModal($event.detail)"
    @keydown.escape.window="close()"
    class="fixed inset-0 z-[500] flex items-center justify-center p-4"
    style="display:none">

    <div class="fixed inset-0 bg-black/50" @click="close()"></div>

    <div class="relative w-full max-w-md rounded-2xl bg-white shadow-xl"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100">

        <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ __('Remove this post') }}</h3>
                <p class="mt-0.5 text-xs text-gray-500">{{ __('The author will be notified with the reason you choose.') }}</p>
            </div>
            <button type="button" @click="close()" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" :action="removeUrl" @submit="submitting = true" class="px-5 py-4">
            @csrf

            <p class="mb-2 text-sm font-medium text-gray-700">{{ __('Reason for removal') }}</p>
            <div class="space-y-1.5">
                @foreach (\App\Models\PostReport::REASONS as $key => $label)
                    <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border px-3 py-2 text-sm transition"
                        :class="reason === '{{ $key }}' ? 'border-red-900 bg-red-50 text-red-900' : 'border-gray-200 text-gray-700 hover:bg-gray-50'">
                        <input type="radio" name="reason" value="{{ $key }}" x-model="reason"
                            class="text-red-900 focus:ring-red-900">
                        <span>{{ __($label) }}</span>
                    </label>
                @endforeach
            </div>

            <label class="mt-4 block text-sm font-medium text-gray-700">
                {{ __('Add a note') }} <span class="font-normal text-gray-400">({{ __('optional') }})</span>
            </label>
            <textarea name="note" rows="3" maxlength="500"
                placeholder="{{ __('Context for the author about why this was removed...') }}"
                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-900 focus:ring-1 focus:ring-red-900"></textarea>

            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="close()"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    {{ __('Cancel') }}
                </button>
                {{-- Don't add `submitting` to :disabled — disabling a submit button
                     in its own click cancels the native form submit. The form's
                     @submit flips the flag for the "Removing..." label instead. --}}
                <button type="submit" :disabled="!reason"
                    class="rounded-lg bg-red-900 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:bg-gray-300">
                    <span x-show="!submitting">{{ __('Remove post') }}</span>
                    <span x-show="submitting" x-cloak>{{ __('Removing...') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function removePostModal() {
        return {
            open: false,
            removeUrl: null,
            reason: '',
            submitting: false,

            openModal(detail) {
                this.removeUrl = detail?.removeUrl || null;
                this.reason = '';
                this.submitting = false;
                this.open = true;
            },

            close() {
                this.open = false;
            },
        };
    }
</script>
