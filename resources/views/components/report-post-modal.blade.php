{{--
    Shared "Report post" modal. Opened from a post's three-dot menu via:
        $dispatch('open-report-modal', { reportUrl: '<posts.report URL>' })
    Include once per page that renders the feed (alongside <x-post-detail-modal />).
--}}
<div x-data="reportPostModal()"
    x-show="open"
    x-cloak
    @open-report-modal.window="openModal($event.detail)"
    @keydown.escape.window="close()"
    class="fixed inset-0 z-[500] flex items-center justify-center p-4"
    style="display:none">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50" @click="close()"></div>

    <!-- Panel -->
    <div class="relative w-full max-w-md rounded-2xl bg-white shadow-xl"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100">

        <div class="flex items-start justify-between border-b border-gray-200 px-5 py-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ __('Report post') }}</h3>
                <p class="mt-0.5 text-xs text-gray-500">{{ __('Tell us what\'s wrong. Our team reviews reports.') }}</p>
            </div>
            <button type="button" @click="close()" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-5 py-4">
            {{-- Success state --}}
            <template x-if="done">
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-6 text-center text-sm text-green-700">
                    <p x-text="successMessage"></p>
                </div>
            </template>

            {{-- Form --}}
            <div x-show="!done">
                <template x-if="error">
                    <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="error"></div>
                </template>

                <p class="mb-2 text-sm font-medium text-gray-700">{{ __('Why are you reporting this?') }}</p>
                <div class="space-y-1.5">
                    @foreach (\App\Models\PostReport::REASONS as $key => $label)
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border px-3 py-2 text-sm transition"
                            :class="reason === '{{ $key }}' ? 'border-red-900 bg-red-50 text-red-900' : 'border-gray-200 text-gray-700 hover:bg-gray-50'">
                            <input type="radio" name="report_reason" value="{{ $key }}" x-model="reason"
                                class="text-red-900 focus:ring-red-900">
                            <span>{{ __($label) }}</span>
                        </label>
                    @endforeach
                </div>

                <label class="mt-4 block text-sm font-medium text-gray-700">
                    {{ __('Add details') }} <span class="font-normal text-gray-400">({{ __('optional') }})</span>
                </label>
                <textarea x-model="details" rows="3" maxlength="500"
                    placeholder="{{ __('Anything that helps us understand the issue...') }}"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-900 focus:ring-1 focus:ring-red-900"></textarea>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="close()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" @click="submit()" :disabled="!reason || submitting"
                        class="rounded-lg bg-red-900 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:bg-gray-300">
                        <span x-show="!submitting">{{ __('Submit report') }}</span>
                        <span x-show="submitting">{{ __('Submitting...') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function reportPostModal() {
        return {
            open: false,
            reportUrl: null,
            reason: '',
            details: '',
            submitting: false,
            done: false,
            error: null,
            successMessage: '',

            openModal(detail) {
                this.reportUrl = detail?.reportUrl || null;
                this.reason = '';
                this.details = '';
                this.error = null;
                this.done = false;
                this.submitting = false;
                this.open = true;
            },

            close() {
                this.open = false;
            },

            submit() {
                if (!this.reason || !this.reportUrl) return;
                this.submitting = true;
                this.error = null;

                fetch(this.reportUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: JSON.stringify({ reason: this.reason, details: this.details }),
                })
                    .then(async (response) => {
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(data.error || data.message || 'Failed to submit report.');
                        }
                        return data;
                    })
                    .then((data) => {
                        this.successMessage = data.message || 'Thank you! Your report has been submitted. Together let\'s keep AlumniHub a safe and wholesome environment for all Teknolohistas.';
                        this.done = true;
                        this.submitting = false;
                        setTimeout(() => { if (this.done) this.close(); }, 2500);
                    })
                    .catch((err) => {
                        this.error = err.message || 'Failed to submit report.';
                        this.submitting = false;
                    });
            },
        };
    }
</script>
