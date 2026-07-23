<x-app-layout>
    <x-slot name="title">{{ $partner->name }}</x-slot>

    @php
        $initialMessages = $messages->map(fn ($m) => [
            'id'        => $m->id,
            'sender_id' => $m->sender_id,
            'body'      => $m->body,
        ])->values();
    @endphp

    <div class="py-6"
        x-data="messageThread({
            conversationId: {{ $conversation->id }},
            authId: {{ $authUser->id }},
            partnerId: {{ $partner->id }},
            storeUrl: '{{ route('messages.store', $partner->id) }}',
            readUrl: '{{ route('messages.read', $partner->id) }}',
            initial: @js($initialMessages),
        })">

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden"
                style="height: calc(100vh - 11rem); min-height: 28rem;">

                {{-- Header --}}
                <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-3">
                    <a href="{{ route('messages.index') }}"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <a href="{{ route('profiles.show', $partner->id) }}" class="flex items-center gap-3 min-w-0">
                        <img src="{{ $partner->profileAvatarUrl() }}" alt="{{ $partner->name }}"
                            class="h-9 w-9 shrink-0 rounded-full object-cover ring-1 ring-gray-200">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900">{{ $partner->name }}</p>
                            <p class="truncate text-xs text-gray-500">
                                {{ $partner->program_course ?? ($partner->batch_year ? 'Batch ' . $partner->batch_year : '') }}
                            </p>
                        </div>
                    </a>
                </div>

                {{-- Message list --}}
                <div x-ref="scroll" class="flex-1 overflow-y-auto px-5 py-4 space-y-2 bg-gray-50">
                    <p x-show="messages.length === 0" class="py-8 text-center text-sm text-gray-400">
                        {{ __('Say hello 👋') }}
                    </p>

                    <template x-for="m in messages" :key="m.id">
                        <div class="flex" :class="m.sender_id === authId ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[75%] rounded-2xl px-3.5 py-2 text-sm leading-relaxed break-words"
                                :class="m.sender_id === authId
                                    ? 'bg-red-900 text-white rounded-br-sm'
                                    : 'bg-white text-gray-800 ring-1 ring-gray-200 rounded-bl-sm'"
                                x-text="m.body"></div>
                        </div>
                    </template>
                </div>

                {{-- Composer --}}
                @if ($canSend)
                    <form @submit.prevent="send()" class="flex items-end gap-2 border-t border-gray-100 px-4 py-3">
                        <textarea x-model="draft" x-ref="input" rows="1" maxlength="5000"
                            @keydown.enter.prevent="if (!$event.shiftKey) send()"
                            placeholder="{{ __('Write a message…') }}"
                            class="flex-1 resize-none rounded-xl border border-gray-200 px-3.5 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:border-red-900 focus:ring-0"></textarea>
                        <button type="submit" :disabled="sending || draft.trim() === ''"
                            :class="(sending || draft.trim() === '') ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-800'"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-900 text-white transition">
                            <i class="fa-solid fa-paper-plane text-sm"></i>
                        </button>
                    </form>
                @else
                    {{-- Disconnected: read-only history, no composer. --}}
                    <div class="border-t border-gray-100 px-5 py-4 text-center text-sm text-gray-500">
                        <i class="fa-regular fa-circle-xmark me-1 text-gray-400"></i>
                        {{ __("You're no longer connected with this person. You can view past messages but can't send new ones.") }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function messageThread(config) {
            return {
                conversationId: config.conversationId,
                authId: config.authId,
                partnerId: config.partnerId,
                storeUrl: config.storeUrl,
                readUrl: config.readUrl,
                messages: config.initial ?? [],
                draft: '',
                sending: false,

                init() {
                    this.$nextTick(() => this.scrollToBottom());

                    // Subscribe to the conversation channel for real-time delivery.
                    if (window.Echo) {
                        window.Echo.private('conversation.' + this.conversationId)
                            .listen('.MessageSent', (e) => {
                                this.addMessage(e);
                                this.markRead();
                            });
                    }
                },

                addMessage(m) {
                    if (!m || m.id == null) return;
                    // Dedupe by id (guards against optimistic + broadcast double-append).
                    if (this.messages.some((x) => x.id === m.id)) return;
                    this.messages.push({ id: m.id, sender_id: m.sender_id, body: m.body });
                    this.$nextTick(() => this.scrollToBottom());
                },

                async send() {
                    const body = this.draft.trim();
                    if (body === '' || this.sending) return;
                    this.sending = true;

                    try {
                        const { data } = await window.axios.post(this.storeUrl, { body });
                        this.addMessage(data.message);
                        this.draft = '';
                        this.$nextTick(() => this.$refs.input.focus());
                    } catch (err) {
                        alert(err?.response?.data?.message ?? 'Failed to send message.');
                    } finally {
                        this.sending = false;
                    }
                },

                markRead() {
                    window.axios.post(this.readUrl)
                        .then(() => window.dispatchEvent(new CustomEvent('message-count-refresh')))
                        .catch(() => {});
                },

                scrollToBottom() {
                    const el = this.$refs.scroll;
                    if (el) el.scrollTop = el.scrollHeight;
                },
            };
        }
    </script>
</x-app-layout>
