{{--
    User Management table with inline (in-place) editing. Rendered on the admin
    dashboard home and the standalone admin.users.index page. Expects: $users
    (paginator), $search, $role, $status, $accountStatuses, $editableRoles,
    $programCourses, $minYear, $maxYear.
--}}
<div class="space-y-6"
    x-data="{
        savingId: null,
        flash: '',
        query: '',
        roleFilter: '',
        statusFilter: '',
        editingId: null,
        forms: {},
        views: {},
        updateUrls: {},
        stats: @js($userStats ?? null),
        startStatsPolling() {
            if (!this.stats) return;
            setInterval(async () => {
                try {
                    const r = await fetch('{{ route('admin.users.stats') }}', {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (r.ok) this.stats = await r.json();
                } catch (e) {}
            }, 30000);
        },
        register(id, data, url) { this.views[id] = { ...data }; this.updateUrls[id] = url; },
        rowVisible(el) {
            if (!this.matches(el.dataset.search)) return false;
            const id = el.dataset.id;
            const v = this.views[id];
            // Privileged/protected rows have no view entry; fall back to data-*.
            const role = v ? v.role : el.dataset.role;
            const status = v ? v.account_status : el.dataset.status;
            if (this.roleFilter && role !== this.roleFilter) return false;
            if (this.statusFilter && status !== this.statusFilter) return false;
            return true;
        },
        start(id) { this.forms[id] = { ...this.views[id] }; this.editingId = id; },
        cancel() { this.editingId = null; },
        async save(id) {
            this.savingId = id;
            try {
                const res = await fetch(this.updateUrls[id], {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.forms[id]),
                });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    alert(err.message || 'Update failed. Please check the values and try again.');
                    return;
                }
                const data = await res.json();
                this.views[id] = { ...data.user };
                this.editingId = null;
                this.flashUser(id);
            } catch (e) {
                alert('Network error. Please try again.');
            } finally {
                this.savingId = null;
            }
        },
        flashUser(id) { this.flash = id; setTimeout(() => { if (this.flash === id) this.flash = ''; }, 2500); },
        matches(haystack) {
            const q = this.query.trim().toLowerCase();
            if (!q) return true;
            return q.split(/\s+/).every(word => (haystack || '').toLowerCase().includes(word));
        },
        get filtersActive() {
            return !!(this.query.trim() || this.roleFilter || this.statusFilter);
        },
        get visibleCount() {
            if (!this.filtersActive) return null;
            return Array.from(this.$root.querySelectorAll('tr[data-main]'))
                .filter(r => this.rowVisible(r)).length;
        },
    }"
    x-init="startStatsPolling()">

    {{-- Analytics: most active communities (by non-trashed post count) --}}
    @isset($communityActivity)
        <div class="overflow-hidden rounded-2xl border border-red-900/10 bg-white shadow-sm">
            {{-- Brand header --}}
            <div class="flex items-center gap-3 bg-gradient-to-r from-red-900 to-red-800 px-5 py-4">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-[#FFC107]">
                    <i class="fa-solid fa-chart-simple"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-white">{{ __('Most Active Communities') }}</h3>
                    <p class="text-xs text-red-100/80">{{ __('Ranked by total posts') }}</p>
                </div>
            </div>

            @php $activityMax = collect($communityActivity)->max('count') ?: 1; @endphp
            @if (count($communityActivity) === 0 || $activityMax === 0)
                <p class="px-5 py-6 text-sm text-gray-500">{{ __('No post activity yet.') }}</p>
            @else
                <div class="space-y-4 px-5 py-5">
                    @foreach ($communityActivity as $i => $row)
                        @php $pct = $row['count'] > 0 ? max(round($row['count'] / $activityMax * 100), 3) : 0; @endphp
                        <div class="flex items-center gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-xs font-bold
                                {{ $i === 0 ? 'bg-[#FFC107] text-red-900' : 'bg-red-900/5 text-red-900/70' }}">{{ $i + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <span class="truncate font-semibold text-gray-800">{{ $row['name'] }}</span>
                                    <span class="shrink-0 font-bold text-red-900">{{ number_format($row['count']) }}</span>
                                </div>
                                <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-gradient-to-r from-red-900 to-red-700 transition-all duration-700"
                                        style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endisset

    {{-- Analytics: user stat cards --}}
    @isset($userStats)
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @php
                $cards = [
                    ['key' => 'all', 'label' => __('All Users'), 'icon' => 'fa-users', 'accent' => 'bg-red-900', 'chip' => 'bg-red-900/10 text-red-900'],
                    ['key' => 'online', 'label' => __('Online'), 'icon' => 'fa-circle-dot', 'accent' => 'bg-emerald-500', 'chip' => 'bg-emerald-100 text-emerald-600'],
                    ['key' => 'offline', 'label' => __('Offline'), 'icon' => 'fa-moon', 'accent' => 'bg-gray-400', 'chip' => 'bg-gray-100 text-gray-500'],
                    ['key' => 'pending', 'label' => __('Pending'), 'icon' => 'fa-hourglass-half', 'accent' => 'bg-[#FFC107]', 'chip' => 'bg-amber-100 text-amber-700'],
                    ['key' => 'approved', 'label' => __('Approved'), 'icon' => 'fa-circle-check', 'accent' => 'bg-teal-500', 'chip' => 'bg-teal-100 text-teal-600'],
                ];
            @endphp
            @foreach ($cards as $card)
                <div class="group relative overflow-hidden rounded-2xl border border-red-900/10 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="absolute inset-x-0 top-0 h-1 {{ $card['accent'] }}"></span>
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $card['chip'] }}">
                            <i class="fa-solid {{ $card['icon'] }} text-lg"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-2xl font-extrabold leading-none text-gray-900"
                                x-text="Number(stats.{{ $card['key'] }} ?? 0).toLocaleString()"></p>
                            <p class="mt-1 truncate text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endisset

    <div class="bg-white shadow sm:rounded-lg p-4 sm:p-6">
        <div class="flex flex-wrap items-start gap-3">
            {{-- Live search (client-side, no reload) --}}
            <div class="relative flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                <div class="relative mt-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                    <input type="text" id="search" x-model="query" autocomplete="off"
                        placeholder="{{ __('Name or email') }}"
                        class="w-full rounded-md border-gray-300 pl-10 pr-9 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                    <button type="button" x-show="query.trim()" @click="query = ''"
                        aria-label="{{ __('Clear search') }}"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-gray-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                {{-- Absolutely positioned so it never shifts the row alignment --}}
                <p class="absolute left-0 top-full mt-1 text-xs text-gray-500" x-show="filtersActive"
                    x-text="visibleCount + ' result' + (visibleCount === 1 ? '' : 's')"></p>
            </div>

            {{-- Role/Status filters (live, client-side — no navigation) --}}
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">{{ __('Role') }}</label>
                <select id="role" x-model="roleFilter"
                    class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                    <option value="">{{ __('All') }}</option>
                    <option value="alumni">{{ __('Alumni') }}</option>
                    <option value="student">{{ __('Student') }}</option>
                    <option value="admin">{{ __('Admin') }}</option>
                    <option value="superadmin">{{ __('Superadmin') }}</option>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                <select id="status" x-model="statusFilter"
                    class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($accountStatuses as $s)
                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-transparent">.</label>
                <button type="button" x-show="filtersActive" @click="query = ''; roleFilter = ''; statusFilter = ''"
                    class="mt-1 inline-flex h-[38px] items-center rounded-md border border-gray-300 px-4 text-xs font-semibold uppercase tracking-widest text-gray-600 transition hover:bg-gray-50">
                    {{ __('Clear') }}
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('First Name') }}</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Last Name') }}</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Email') }}</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Role') }}</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Status') }}</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Batch') }}</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Program') }}</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Presence') }}</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        @php
                            $isProtected = in_array($user->role, ['admin', 'superadmin'], true);
                            $uid = $user->id;
                        @endphp
                        @unless ($isProtected)
                            <template x-init="register({{ $uid }}, {
                                role: @js($user->role),
                                account_status: @js($user->account_status),
                                batch_year: @js((int) $user->batch_year),
                                program_course: @js($user->program_course),
                            }, '{{ route('admin.users.update', $user) }}')"></template>
                        @endunless

                        {{-- Main row: always badges, never cramped --}}
                        <tr data-main data-search="{{ \Illuminate\Support\Str::lower(trim($user->first_name . ' ' . $user->last_name . ' ' . $user->name . ' ' . $user->email)) }}"
                            data-id="{{ $uid }}" data-role="{{ $user->role }}" data-status="{{ $user->account_status }}"
                            x-show="rowVisible($el)"
                            :class="flash === {{ $uid }} ? 'bg-emerald-50 transition' : ''">
                            <td class="px-4 py-3 text-gray-900">{{ $user->first_name ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-900">{{ $user->last_name ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $user->email }}</td>

                            {{-- Role --}}
                            <td class="px-4 py-3">
                                @if ($isProtected)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-700">{{ ucfirst($user->role) }}</span>
                                @else
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700"
                                        x-text="(views[{{ $uid }}]?.role || '').charAt(0).toUpperCase() + (views[{{ $uid }}]?.role || '').slice(1)"></span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3">
                                @if ($isProtected)
                                    <span class="text-gray-400">{{ ucfirst($user->account_status ?: '—') }}</span>
                                @else
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-emerald-100 text-emerald-700': views[{{ $uid }}]?.account_status === 'approved',
                                            'bg-amber-100 text-amber-700': views[{{ $uid }}]?.account_status === 'pending',
                                            'bg-rose-100 text-rose-700': views[{{ $uid }}]?.account_status === 'rejected',
                                        }"
                                        x-text="(views[{{ $uid }}]?.account_status || '').charAt(0).toUpperCase() + (views[{{ $uid }}]?.account_status || '').slice(1)"></span>
                                @endif
                            </td>

                            {{-- Batch year --}}
                            <td class="px-4 py-3 text-gray-700">
                                @if ($isProtected)
                                    {{ $user->batch_year ?: '—' }}
                                @else
                                    <span x-text="views[{{ $uid }}]?.batch_year || '—'"></span>
                                @endif
                            </td>

                            {{-- Program --}}
                            <td class="px-4 py-3 text-gray-700">
                                @if ($isProtected)
                                    {{ $user->program_course ?: '—' }}
                                @else
                                    <span x-text="views[{{ $uid }}]?.program_course || '—'"></span>
                                @endif
                            </td>

                            {{-- Presence --}}
                            <td class="px-4 py-3">
                                @if ($user->isOnline())
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>{{ __('Online') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                                        <span class="h-2 w-2 rounded-full bg-gray-300"></span>{{ __('Offline') }}
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($isProtected)
                                    <span class="text-xs text-gray-400">{{ __('Protected') }}</span>
                                @else
                                    <button type="button" @click="editingId === {{ $uid }} ? cancel() : start({{ $uid }})"
                                        class="text-sm font-semibold text-red-900 hover:text-red-700 hover:underline"
                                        x-text="editingId === {{ $uid }} ? '{{ __('Close') }}' : '{{ __('Edit') }}'"></button>
                                @endif
                            </td>
                        </tr>

                        {{-- Expanding edit panel: full-width, roomy, full text --}}
                        @unless ($isProtected)
                            <tr data-id="{{ $uid }}" data-role="{{ $user->role }}" data-status="{{ $user->account_status }}"
                                data-search="{{ \Illuminate\Support\Str::lower(trim($user->first_name . ' ' . $user->last_name . ' ' . $user->name . ' ' . $user->email)) }}"
                                x-show="editingId === {{ $uid }} && rowVisible($el)" x-cloak>
                                <td colspan="9" class="border-l-4 border-red-900 bg-red-900/[0.03] px-4 py-4">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Role') }}</label>
                                            <select x-model="forms[{{ $uid }}].role"
                                                class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                                @foreach ($editableRoles as $r)
                                                    <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Status') }}</label>
                                            <select x-model="forms[{{ $uid }}].account_status"
                                                class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                                @foreach ($accountStatuses as $s)
                                                    <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Batch Year') }}</label>
                                            <select x-model.number="forms[{{ $uid }}].batch_year"
                                                class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                                @for ($y = $maxYear; $y >= $minYear; $y--)
                                                    <option value="{{ $y }}">{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Program / Course') }}</label>
                                            <select x-model="forms[{{ $uid }}].program_course"
                                                class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                                @foreach ($programCourses as $course)
                                                    <option value="{{ $course }}">{{ $course }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex items-center justify-end gap-2">
                                        <button type="button" @click="save({{ $uid }})" :disabled="savingId === {{ $uid }}"
                                            class="rounded-md bg-red-900 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2 disabled:opacity-50">
                                            <span x-show="savingId !== {{ $uid }}">{{ __('Save') }}</span>
                                            <span x-show="savingId === {{ $uid }}">{{ __('Saving…') }}</span>
                                        </button>
                                        <button type="button" @click="cancel()" :disabled="savingId === {{ $uid }}"
                                            class="rounded-md border border-gray-300 bg-white px-5 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 disabled:opacity-50">
                                            {{ __('Cancel') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endunless
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-gray-500">{{ __('No users found.') }}</td>
                        </tr>
                    @endforelse

                    {{-- Live "no matches" row (search hid every row on this page) --}}
                    @if ($users->count() > 0)
                        <tr x-show="filtersActive && visibleCount === 0">
                            <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                {{ __('No matches for') }} "<span x-text="query"></span>".
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
