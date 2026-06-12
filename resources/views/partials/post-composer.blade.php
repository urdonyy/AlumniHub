                        <div class="{{ ($composerEditOnly ?? false) ? '' : 'rounded-2xl border border-gray-200 bg-white p-5 shadow-sm' }}"
                            x-data="postComposer(@js($flairsByCommunity ?? []), {{ $defaultCommunityId ?? 'null' }}, @js(['lockedCommunityId' => $composerLockedCommunityId ?? null, 'allowedVisibilities' => $composerVisibilities ?? ['public', 'connections', 'members'], 'generalHubId' => $composerGeneralHubId ?? null]))">
                            {{-- Edit-only mode (e.g. profile page) renders just the modal, no create card. --}}
                            @unless ($composerEditOnly ?? false)
                            <button type="button"
                                @click="open = true"
                                class="w-full rounded-full border border-gray-300 bg-gray-50 px-5 py-3 text-left text-sm text-gray-500 transition hover:bg-gray-100">
                                What's on your mind, {{ auth()->user()->name }}?
                            </button>
                            @endunless

                            <div x-show="open"
                                x-transition.opacity
                                @keydown.escape.window="closeAndReset()"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                style="display: none;">
                                <div @click.away="closeAndReset()"
                                    class="w-full max-w-xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl flex flex-col max-h-[90vh]">

                                    <!-- Modal header -->
                                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
                                        <h3 class="text-sm font-semibold text-gray-900" x-text="editMode ? 'Edit post' : 'Create post'"></h3>
                                        <button type="button" @click="closeAndReset()"
                                            class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <form method="post" :action="editMode ? editUrl : '{{ route('posts.quick-store') }}'"
                                        enctype="multipart/form-data" class="flex flex-col overflow-y-auto flex-1"
                                        @submit.prevent="submitPost($el)">
                                        @csrf
                                        <input type="hidden" name="_method" :value="editMode ? 'PATCH' : 'POST'">
                                        <input type="hidden" name="community_id" :value="isConnectionsOnly ? '' : communityId">
                                        <input type="hidden" name="visibility" :value="visibility">
                                        <input type="hidden" name="post_type" :value="postType">
                                        <input type="hidden" name="title" :value="titleValue">
                                        <input type="hidden" name="body_markdown" :value="bodyValue">
                                        {{-- Event-only fields (ignored by validation for text/media posts) --}}
                                        <input type="hidden" name="event_type" :value="eventType">
                                        <input type="hidden" name="starts_at" :value="startsAtValue">
                                        <input type="hidden" name="ends_at" :value="endsAtValue">
                                        <input type="hidden" name="external_link" :value="externalLink">
                                        <input type="hidden" name="address" :value="address">
                                        <input type="hidden" name="venue" :value="venue">
                                        <input type="hidden" name="auto_invite" :value="autoInvite ? 1 : 0">

                                        <!-- Avatar + Name + Audience (stacked under name) -->
                                        <div class="flex items-start gap-3 px-5 pt-4 pb-3">
                                            <img src="{{ auth()->user()->profileAvatarUrl() }}"
                                                alt="{{ auth()->user()->name }}"
                                                class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover"
                                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-sm font-semibold text-gray-900 leading-none">{{ auth()->user()->name }}</span>

                                                <!-- Audience indicator. Clickable picker when there are multiple options;
                                                     a static label (e.g. "Everyone" on General Hub) when there's only one. -->
                                                <div class="relative">
                                                    <button type="button"
                                                        @click="effectiveVisibilities.length > 1 && (audienceOpen = !audienceOpen)"
                                                        :class="effectiveVisibilities.length > 1 ? 'cursor-pointer hover:bg-gray-50' : 'cursor-default'"
                                                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-sm transition">
                                                        <template x-if="visibility === 'public' || (visibility === 'members' && isGeneralHubSelected)">
                                                            <svg class="h-3 w-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        </template>
                                                        <template x-if="visibility === 'connections'">
                                                            <svg class="h-3 w-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            </svg>
                                                        </template>
                                                        <template x-if="visibility === 'members' && !isGeneralHubSelected">
                                                            <svg class="h-3 w-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                            </svg>
                                                        </template>
                                                        <span x-text="(visibility === 'members' && isGeneralHubSelected) ? 'Everyone' : {'public': 'Public', 'connections': 'Connections', 'members': 'Community'}[visibility]"></span>
                                                        <svg x-show="effectiveVisibilities.length > 1" class="h-2.5 w-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>

                                                    <!-- Audience dropdown -->
                                                    <div x-show="audienceOpen"
                                                        @click.away="audienceOpen = false"
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="opacity-0 translate-y-1"
                                                        x-transition:enter-end="opacity-100 translate-y-0"
                                                        x-transition:leave="transition ease-in duration-75"
                                                        x-transition:leave-start="opacity-100 translate-y-0"
                                                        x-transition:leave-end="opacity-0 translate-y-1"
                                                        class="absolute left-0 top-full mt-1 w-72 rounded-xl border border-gray-200 bg-white shadow-lg z-20">
                                                        <div class="p-1.5">
                                                            <p class="px-2 pt-1.5 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">Who can see your post?</p>
                                                            <p x-show="isEvent" class="px-2 pb-1 text-[11px] text-gray-400">Events can be shared with your connections or a community only.</p>

                                                            <button type="button" @click="onVisibilityChange('public')"
                                                                x-show="!isEvent && effectiveVisibilities.includes('public')"
                                                                class="w-full flex items-start gap-3 rounded-lg px-3 py-2.5 text-left transition hover:bg-gray-50"
                                                                :class="{ 'bg-red-50 hover:bg-red-50': visibility === 'public' }">
                                                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100">
                                                                    <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                    </svg>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="flex items-center justify-between">
                                                                        <span class="text-sm font-medium text-gray-900">Public</span>
                                                                        <svg x-show="visibility === 'public'" class="h-4 w-4 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    </div>
                                                                    <p class="text-xs text-gray-500 mt-0.5">Anyone on AlumniHub can see this post</p>
                                                                </div>
                                                            </button>

                                                            <button type="button" @click="onVisibilityChange('connections')"
                                                                x-show="effectiveVisibilities.includes('connections')"
                                                                class="w-full flex items-start gap-3 rounded-lg px-3 py-2.5 text-left transition hover:bg-gray-50"
                                                                :class="{ 'bg-red-50 hover:bg-red-50': visibility === 'connections' }">
                                                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100">
                                                                    <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                    </svg>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="flex items-center justify-between">
                                                                        <span class="text-sm font-medium text-gray-900">Connections</span>
                                                                        <svg x-show="visibility === 'connections'" class="h-4 w-4 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    </div>
                                                                    <p class="text-xs text-gray-500 mt-0.5">Only people you are connected with will see this</p>
                                                                </div>
                                                            </button>

                                                            <button type="button" @click="onVisibilityChange('members')"
                                                                x-show="effectiveVisibilities.includes('members')"
                                                                class="w-full flex items-start gap-3 rounded-lg px-3 py-2.5 text-left transition hover:bg-gray-50"
                                                                :class="{ 'bg-red-50 hover:bg-red-50': visibility === 'members' }">
                                                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100">
                                                                    <template x-if="!isGeneralHubSelected">
                                                                        <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                                        </svg>
                                                                    </template>
                                                                    <template x-if="isGeneralHubSelected">
                                                                        <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                        </svg>
                                                                    </template>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="flex items-center justify-between">
                                                                        <span class="text-sm font-medium text-gray-900" x-text="isGeneralHubSelected ? 'Everyone' : 'Community'"></span>
                                                                        <svg x-show="visibility === 'members'" class="h-4 w-4 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    </div>
                                                                    <p class="text-xs text-gray-500 mt-0.5" x-text="isGeneralHubSelected ? 'Everyone on AlumniHub can see this' : 'Only members of the selected community can see this'"></p>
                                                                </div>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Community selector (hidden when connections-only or community is locked) -->
                                        <div x-show="!isConnectionsOnly && !lockedCommunityId" class="px-5 pb-3">
                                            <select name="community_selector" x-model="communityId" @change="communityError = false" aria-placeholder="Select a community"
                                                :class="communityError ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-300'"
                                                class="w-full rounded-md bg-white py-1.5 pl-3 pr-8 text-xs font-medium text-gray-700 shadow-sm focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900">
                                                <option value="" disabled selected hidden>Select a community</option>
                                                @foreach (($joinedCommunitiesCollection ?? collect()) as $joinedCommunity)
                                                    <option value="{{ $joinedCommunity->id }}"
                                                        @selected($defaultCommunityId == $joinedCommunity->id)>
                                                        {{ $joinedCommunity->name }}</option>
                                                @endforeach
                                            </select>
                                            <p x-show="communityError" x-cloak class="mt-1.5 text-xs text-red-600">{{ __('Please select a community for this post.') }}</p>
                                        </div>

                                        <!-- Connections-only info banner -->
                                        <div x-show="isConnectionsOnly" style="display:none;" class="mx-5 mb-3 flex items-start gap-2.5 rounded-lg border border-blue-100 bg-blue-50 px-3.5 py-3">
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div class="text-xs text-blue-800 leading-relaxed">
                                                <span class="font-semibold">Connections only</span>: Your post will be visible only to people in your connections list. It will not appear in community feeds, public discovery, or to anyone outside your network.
                                            </div>
                                        </div>

                                        <!-- Post type selector (icons only, hidden in edit mode) -->
                                        <div class="px-5 pb-3" x-show="!editMode">
                                            <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">Post type</p>
                                            <div class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-gray-50 p-1">
                                                <button type="button" @click="setPostType('text')"
                                                    title="Text post" aria-label="Text post"
                                                    class="flex h-9 w-9 items-center justify-center rounded-lg transition"
                                                    :class="postType === 'text' ? 'bg-red-900 text-white shadow-sm' : 'text-gray-500 hover:bg-white hover:text-gray-700'">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/>
                                                    </svg>
                                                </button>
                                                <button type="button" @click="setPostType('media')"
                                                    title="Media post" aria-label="Media post"
                                                    class="flex h-9 w-9 items-center justify-center rounded-lg transition"
                                                    :class="postType === 'media' ? 'bg-red-900 text-white shadow-sm' : 'text-gray-500 hover:bg-white hover:text-gray-700'">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </button>
                                                <button type="button" @click="setPostType('event')"
                                                    x-show="effectiveVisibilities.includes('members') || effectiveVisibilities.includes('connections')"
                                                    title="Event post" aria-label="Event post"
                                                    class="flex h-9 w-9 items-center justify-center rounded-lg transition"
                                                    :class="postType === 'event' ? 'bg-red-900 text-white shadow-sm' : 'text-gray-500 hover:bg-white hover:text-gray-700'">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Divider -->
                                        <div class="mx-5 border-t border-gray-100 mb-3"></div>

                                        @php $inputClass = 'w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900'; @endphp

                                        <!-- Title input (text / media) -->
                                        <div x-show="!isEvent" class="px-5 pb-2">
                                            <input type="text" x-model="titleValue"
                                                class="w-full border-0 border-b border-gray-200 bg-transparent pb-1.5 text-sm font-semibold text-gray-900 placeholder:font-normal placeholder:text-gray-400 focus:border-gray-400 focus:outline-none focus:ring-0"
                                                placeholder="Add a title (optional)">
                                        </div>

                                        <!-- Body area (text / media) -->
                                        <div x-show="!isEvent" class="px-5 pb-3 flex flex-col gap-2">
                                            <textarea x-model="bodyValue" :required="!isEvent" :rows="isMedia ? 2 : 4"
                                                class="w-full border-0 bg-transparent text-sm text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-0 resize-none leading-relaxed"
                                                placeholder="What's on your mind, {{ auth()->user()->name }}?"></textarea>
                                        </div>

                                        <!-- Event sub-form -->
                                        <div x-show="isEvent" class="px-5 pb-3 flex flex-col gap-3" style="display:none;">
                                            <!-- Event type: online / in person -->
                                            <div>
                                                <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">Event type</p>
                                                <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
                                                    <button type="button" @click="eventType = 'online'"
                                                        class="rounded-md px-4 py-1.5 text-sm font-medium transition flex gap-1.5 items-center"
                                                        :class="eventType === 'online' ? 'bg-red-900 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'">
                                                        <i class="fas fa-globe"></i><span>Online</span>
                                                    </button>
                                                    <button type="button" @click="eventType = 'in_person'"
                                                        class="rounded-md px-4 py-1.5 text-sm font-medium transition flex gap-1.5 items-center"
                                                        :class="eventType === 'in_person' ? 'bg-red-900 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800'">
                                                        <i class="fas fa-map-marker-alt"></i><span>In person</span>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Event name -->
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">Event name <span class="text-red-500">*</span></label>
                                                <input type="text" x-model="titleValue" :required="isEvent"
                                                    placeholder="e.g. Alumni Homecoming 2026" class="{{ $inputClass }}">
                                            </div>

                                            <!-- Start date / time -->
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">Start date <span class="text-red-500">*</span></label>
                                                    <input type="date" x-model="startDate" :required="isEvent" @change="eventDateError = false" class="{{ $inputClass }}">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">Start time <span class="text-red-500">*</span></label>
                                                    <input type="time" x-model="startTime" :required="isEvent" @change="eventDateError = false" class="{{ $inputClass }}">
                                                </div>
                                            </div>

                                            <p x-show="eventDateError" class="text-xs font-medium text-red-600">Start date and time must be in the future.</p>

                                            <!-- Add end date toggle -->
                                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                                <input type="checkbox" x-model="hasEndDate"
                                                    class="h-4 w-4 rounded border-gray-300 text-red-900 accent-red-900 focus:ring-red-900">
                                                Add end date and time
                                            </label>

                                            <!-- End date / time -->
                                            <div x-show="hasEndDate" style="display:none;" class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">End date</label>
                                                    <input type="date" x-model="endDate" :required="isEvent && hasEndDate" class="{{ $inputClass }}">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">End time</label>
                                                    <input type="time" x-model="endTime" :required="isEvent && hasEndDate" class="{{ $inputClass }}">
                                                </div>
                                            </div>

                                            <!-- Address + venue (in person only) -->
                                            <template x-if="eventType === 'in_person'">
                                                <div class="flex flex-col gap-3">
                                                    <div>
                                                        <label class="mb-1 block text-xs font-medium text-gray-600">Address <span class="text-red-500">*</span></label>
                                                        <input type="text" x-model="address" :required="isEvent && eventType === 'in_person'"
                                                            placeholder="e.g. street, city, postal code" class="{{ $inputClass }}">
                                                    </div>
                                                    <div>
                                                        <label class="mb-1 block text-xs font-medium text-gray-600">Venue <span class="text-gray-400 font-normal">(optional)</span></label>
                                                        <input type="text" x-model="venue"
                                                            placeholder="e.g. floor / room number" class="{{ $inputClass }}">
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- External event link -->
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">
                                                    External event link
                                                    <template x-if="eventType === 'online'"><span class="text-red-500">*</span></template>
                                                    <template x-if="eventType === 'in_person'"><span class="text-gray-400 font-normal">(optional)</span></template>
                                                </label>
                                                <input type="url" x-model="externalLink" :required="isEvent && eventType === 'online'"
                                                    placeholder="https://" class="{{ $inputClass }}">
                                            </div>

                                            <!-- Description -->
                                            <div>
                                                <label class="mb-1 block text-xs font-medium text-gray-600">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                                                <textarea x-model="bodyValue" rows="3"
                                                    placeholder="Add details about your event" class="{{ $inputClass }} resize-none"></textarea>
                                            </div>

                                            <!-- Email auto-invites -->
                                            <label class="flex items-start gap-2.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 cursor-pointer">
                                                <input type="checkbox" x-model="autoInvite"
                                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-red-900 accent-red-900 focus:ring-red-900">
                                                <span class="text-xs leading-relaxed text-gray-600">
                                                    <span class="font-semibold text-gray-800">Email auto-invites</span><br>
                                                    Invite everyone who can see this event, your
                                                    <span x-text="visibility === 'connections' ? 'connections' : 'community members'"></span>
                                                    get an email and an in-app notification.
                                                </span>
                                            </label>
                                        </div>

                                        <!-- Shared image block (media + event) -->
                                        <div x-show="isMedia || isEvent" style="display:none;" class="px-5 pb-3 flex flex-col gap-2">
                                            <!-- Existing media (edit mode) with per-image remove -->
                                            <template x-if="editMode && visibleExistingMedia.length > 0">
                                                <div>
                                                    <p class="mb-1.5 text-xs font-medium text-gray-500">Current images</p>
                                                    <div :class="visibleExistingMedia.length === 1 ? '' : 'grid grid-cols-2 gap-2'">
                                                        <template x-for="m in visibleExistingMedia" :key="m.id">
                                                            <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                                                <img :src="m.url" alt=""
                                                                    :class="visibleExistingMedia.length === 1 ? 'w-full max-h-72 object-contain' : 'w-full h-40 object-cover'">
                                                                <button type="button" @click="removedMediaIds.push(m.id)"
                                                                    class="absolute top-2 right-2 flex h-7 w-7 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/75 transition"
                                                                    aria-label="Remove image">
                                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-for="id in removedMediaIds" :key="id">
                                                <input type="hidden" name="removed_media[]" :value="id">
                                            </template>

                                            <!-- Image preview (Facebook-style) -->
                                            <div id="imagePreviewContainer" class="hidden relative rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
                                                <button type="button" id="removeImageBtn"
                                                    class="absolute top-2 right-2 z-10 flex h-7 w-7 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/75 transition">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                                <img id="singleImagePreview" src="" alt="" class="hidden w-full max-h-56 object-cover">
                                                <div id="multiImageGrid" class="hidden gap-0.5"></div>
                                            </div>

                                            <!-- Attach image button -->
                                            <label class="self-start cursor-pointer inline-flex items-center gap-1.5 rounded-md border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                                                <svg class="h-3.5 w-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                Attach Image
                                                <input type="file" name="attachments[]" id="imageUploadInput" multiple accept="image/*"
                                                    @change="handleImageUpload($event)"
                                                    class="sr-only">
                                            </label>
                                        </div>

                                        <!-- Flair tags (required, 1â€“3, hidden when connections-only or event) -->
                                        <template x-if="filteredFlairs.length > 0 && !isConnectionsOnly && !isEvent">
                                            <div class="px-5 pb-3 pt-3 border-t border-gray-100">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                        Flair tag <span class="text-red-500 normal-case font-normal">* required</span>
                                                    </span>
                                                    <span class="text-xs text-gray-400" x-text="`${selectedFlairs.length} / 3 selected`"></span>
                                                </div>

                                                <p x-show="flairError" class="mb-2 text-xs font-medium text-red-600">Please select at least 1 flair tag (max 3).</p>

                                                <div class="flex flex-wrap gap-1.5">
                                                    <template x-for="flair in visibleFlairs" :key="flair.id">
                                                        <button type="button"
                                                            @click="toggleFlair(flair.id)"
                                                            :disabled="!canSelectFlair(flair.id)"
                                                            class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium transition"
                                                            :class="{
                                                                'border-red-900 bg-red-900 text-white shadow-sm': selectedFlairs.includes(flair.id),
                                                                'border-gray-300 bg-white text-gray-700 hover:border-gray-400 hover:bg-gray-50': !selectedFlairs.includes(flair.id) && canSelectFlair(flair.id),
                                                                'border-gray-200 bg-gray-50 text-gray-300 cursor-not-allowed': !canSelectFlair(flair.id)
                                                            }">
                                                            <span x-show="flair.icon" x-text="flair.icon" class="leading-none"></span>
                                                            <span x-text="flair.name"></span>
                                                        </button>
                                                    </template>

                                                    <template x-if="filteredFlairs.length > 4">
                                                        <button type="button" @click="flairsExpanded = !flairsExpanded"
                                                            class="inline-flex items-center gap-1 rounded-full border border-dashed border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-500 hover:bg-gray-50 transition">
                                                            <span x-text="flairsExpanded ? 'Show less' : `+${filteredFlairs.length - 4} more`"></span>
                                                        </button>
                                                    </template>
                                                </div>

                                                <!-- Hidden inputs for form submission -->
                                                <template x-for="id in selectedFlairs" :key="id">
                                                    <input type="hidden" name="flairs[]" :value="id">
                                                </template>
                                            </div>
                                        </template>

                                        <!-- Footer -->
                                        <div class="border-t border-gray-100 px-5 py-3 flex items-center justify-end">
                                            <button type="submit"
                                                :disabled="isSubmitting"
                                                :class="isSubmitting ? 'opacity-60 cursor-not-allowed' : 'hover:bg-red-800'"
                                                class="rounded-lg bg-red-900 px-7 py-2 text-sm font-semibold text-white transition">
                                                <span x-show="!isSubmitting" x-text="editMode ? 'Save changes' : 'Post'"></span>
                                                <span x-show="isSubmitting" x-text="editMode ? 'Saving…' : 'Posting…'"></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>{{-- end composer card --}}
