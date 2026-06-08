<x-app-layout>
    <x-slot name="title">{{ $profileUser->name }}</x-slot>
    <div class="bg-cover bg-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0"
        style="background-image: url('{{ asset('images/element.png') }}');">
        <x-slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-semibold text-xl text-red-900 leading-tight inline-block lg:hidden">
                    {{ __('Profile') }}
                </h2>
                <p class="text-sm text-red-900">{{ __('Public identity and alumni details') }}</p>
            </div>
        </x-slot>

        <div class="pb-24">
            <div class="max-w-5xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
                @php
                    $isOwnProfile = $viewer->is($profileUser);
                    $canSeeCareerDetails = $isOwnProfile || $showFullDetails;
                @endphp

                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="relative h-40 sm:h-52 lg:h-60">
                        @if ($isOwnProfile)
                            <button type="button" data-profile-media-open
                                class="group relative block h-full w-full text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-900">
                                <img src="{{ $profileUser->profileBannerUrl() }}"
                                    alt="{{ __('Profile banner for :name', ['name' => $profileUser->name]) }}"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-banner.svg') }}';"
                                    class="h-full w-full object-cover transition duration-150 group-hover:scale-[1.01]" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent transition group-hover:from-black/55"></div>
                                <div class="absolute right-4 top-4 inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-red-900 shadow-sm backdrop-blur">
                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    <span>{{ __('Change Banner') }}</span>
                                </div>
                            </button>
                        @else
                            <img src="{{ $profileUser->profileBannerUrl() }}"
                                alt="{{ __('Profile banner for :name', ['name' => $profileUser->name]) }}"
                                onerror="this.onerror=null;this.src='{{ asset('images/default-banner.svg') }}';"
                                class="h-full w-full object-cover" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent"></div>
                        @endif
                    </div>

                    <div class="relative px-6 pb-6 pt-20 sm:px-8 sm:pb-8 sm:pt-24">
                        @if ($isOwnProfile)
                            <button type="button" data-profile-media-open
                                class="absolute -top-14 left-1/2 h-28 w-28 -translate-x-1/2 overflow-hidden rounded-full border-4 border-white bg-white shadow-lg transition hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2 sm:-top-16 sm:h-32 sm:w-32 lg:left-8 lg:translate-x-0">
                                <img src="{{ $profileUser->profileAvatarUrl() }}"
                                    alt="{{ __('Profile photo for :name', ['name' => $profileUser->name]) }}"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';"
                                    class="h-full w-full object-cover" />
                                <span class="absolute inset-x-0 bottom-0 flex items-center justify-center bg-black/50 py-1 text-[10px] font-semibold uppercase tracking-wide text-white">
                                    {{ __('Edit Photo') }}
                                </span>
                            </button>
                        @else
                            <div
                                class="absolute -top-14 left-1/2 h-28 w-28 -translate-x-1/2 overflow-hidden rounded-full border-4 border-white bg-white shadow-lg sm:-top-16 sm:h-32 sm:w-32 lg:left-8 lg:translate-x-0">
                                <img src="{{ $profileUser->profileAvatarUrl() }}"
                                    alt="{{ __('Profile photo for :name', ['name' => $profileUser->name]) }}"
                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';"
                                    class="h-full w-full object-cover" />
                            </div>
                        @endif

                        <div class="flex flex-col gap-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="text-center sm:text-left">
                                    <h3 class="text-2xl font-semibold text-gray-900 sm:text-3xl">
                                        {{ $profileUser->name }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600 sm:text-base">
                                        {{ $profileUser->isInstitution() ? __('Official account') : ($profileUser->program_course ?? __('Program not yet provided')) }}
                                    </p>
                                    <div class="mt-3 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                                        <span
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $profileUser->accountStatusBadgeClass() }}">
                                            {{ $profileUser->accountStatusLabel() }}
                                        </span>
                                        <span
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $profileUser->profileVisibilityBadgeClass() }}">
                                            {{ $profileUser->profileVisibilityLabel() }}
                                        </span>
                                    </div>
                                </div>

                                @if ($isOwnProfile)
                                    <div class="flex items-center gap-2 self-end lg:self-auto">
                                        {{-- Deleted posts bin --}}
                                        <a href="{{ route('profile.post-trash') }}"
                                            class="inline-flex items-center justify-center px-3 py-1 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase whitespace-nowrap tracking-widest hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2 transition ease-in-out duration-150 max-[1024px]:h-8 max-[1024px]:w-8 max-[1024px]:rounded-full max-[1024px]:px-0 max-[1024px]:py-0">
                                            <svg class="h-4 w-4 min-[1025px]:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <span class="sr-only min-[1025px]:not-sr-only hidden min-[1025px]:inline">{{ __('Deleted Posts') }}</span>
                                        </a>
                                        {{-- Edit profile settings (not for the institution account) --}}
                                        @unless ($profileUser->isInstitution())
                                        <a href="{{ route('profile.edit', ['section' => 'account-status']) }}"
                                            class="inline-flex items-center justify-center px-3 py-1 bg-red-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase whitespace-nowrap tracking-widest hover:bg-white hover:text-red-900 hover:border-red-900 focus:bg-white focus:text-red-900 focus:border-red-900 active:bg-white focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2 transition ease-in-out duration-150 max-[1024px]:h-8 max-[1024px]:w-8 max-[1024px]:rounded-full max-[1024px]:px-0 max-[1024px]:py-0">
                                            <i class="fa-solid fa-user-pen text-sm min-[1025px]:hidden" aria-hidden="true"></i>
                                            <span class="sr-only">{{ __('Edit Profile Settings') }}</span>
                                            <span class="hidden min-[1025px]:inline">{{ __('Edit Profile Settings') }}</span>
                                        </a>
                                        @endunless
                                    </div>
                                @elseif ($viewer->canSendConnectionRequests() && $profileUser->isVerified())
                                    @if ($connectionState === 'connected')
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('messages.show', $profileUser) }}"
                                                class="inline-flex items-center gap-1.5 rounded-md bg-red-900 px-3 py-1 sm:px-5 sm:py-1.5 text-xs font-semibold tracking-widest text-white hover:bg-red-800 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                </svg>
                                                {{ __('Message') }}
                                            </a>
                                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                            <button type="button" @click="open = !open"
                                                class="inline-flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-1 sm:px-5 sm:py-1.5 text-xs font-semibold tracking-widest text-emerald-700 hover:bg-emerald-100 transition">
                                                {{ __('Connected') }}
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                            <div x-show="open"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute right-0 top-full mt-1 z-20 w-48 rounded-xl border border-gray-200 bg-white shadow-lg"
                                                style="display:none;">
                                                <form method="POST" action="{{ route('connections.remove', $activeConnection) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-full rounded-xl px-4 py-2.5 text-left text-sm font-medium text-red-700 hover:bg-red-50 transition">
                                                        {{ __('Remove connection') }}
                                                    </button>
                                                </form>
                                            </div>
                                            </div>{{-- end Connected dropdown --}}
                                        </div>{{-- end message + connected row --}}
                                    @elseif ($connectionState === 'invite_sent' && $activeConnection)
                                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                            <button type="button" @click="open = !open"
                                                class="inline-flex items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1 sm:px-5 sm:py-1.5 text-xs font-semibold tracking-widest text-amber-700 hover:bg-amber-100 transition">
                                                {{ __('Invited') }}
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                            <div x-show="open"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="absolute right-0 top-full mt-1 z-20 w-48 rounded-xl border border-gray-200 bg-white shadow-lg"
                                                style="display:none;">
                                                <form method="POST" action="{{ route('connections.withdraw', $activeConnection) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-full rounded-xl px-4 py-2.5 text-left text-sm font-medium text-red-700 hover:bg-red-50 transition">
                                                        {{ __('Withdraw invite') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @elseif ($connectionState === 'invite_received' && $activeConnection)
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('connections.accept', $activeConnection) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-md bg-red-900 px-3 py-1 sm:px-4 sm:py-1.5 text-xs font-semibold tracking-widest text-white hover:bg-red-800">
                                                    {{ __('Accept') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('connections.ignore', $activeConnection) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-md border border-gray-300 px-3 py-1 sm:px-4 sm:py-1.5 text-xs font-semibold tracking-widest text-gray-700 hover:bg-gray-50">
                                                    {{ __('Ignore') }}
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('connections.invite', $profileUser) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center justify-center rounded-md bg-red-900 px-3 py-1 sm:px-5 sm:py-1.5 text-xs font-semibold tracking-widest text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-900 focus:ring-offset-2">
                                                {{ __('Connect') }}
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>

                            <div
                                class="grid gap-1 sm:gap-3 grid-cols-2 {{ $profileUser->isInstitution() ? 'sm:grid-cols-2' : 'sm:grid-cols-3' }} sm:border sm:border-gray-200 sm:bg-gray-50 sm:rounded-lg">
                                @unless ($profileUser->isInstitution())
                                <div
                                    class="p-0 sm:p-4 flex flex-col sm:flex-col gap-[6px] sm:gap-0 items-center justify-center relative min-w-0">
                                    <div class="flex items-center justify-center gap-1">
                                        <i
                                            class="fa-solid fa-graduation-cap text-red-900 text-xs sm:text-sm lg:text-xl"></i>
                                        <p class="text-sm sm:text-lg lg:text-2xl font-semibold text-red-900">
                                            {{ $profileUser->batch_year ?? __('Not provided') }}
                                        </p>
                                    </div>
                                    <p
                                        class="min-w-0 truncate text-xs lg:text-base font-semibold uppercase tracking-wide text-[#FFC107]">
                                        {{ __('Batch Year') }}
                                    </p>
                                    <div
                                        class="border border-red-900 absolute h-[50%] right-0 hidden sm:flex flex-col items-center">
                                    </div>
                                </div>
                                @endunless
                                <div
                                    class="p-0 sm:p-4 flex flex-col sm:flex-col gap-[6px] sm:gap-0 items-center justify-center relative min-w-0 {{ $profileUser->isInstitution() ? '' : 'border-l border-red-900/30 sm:border-l-0' }}">
                                    <div class="flex items-center justify-center gap-1">
                                        <i
                                            class="fa-solid fa-circle-nodes text-red-900 text-xs sm:text-sm lg:text-xl"></i>
                                        <p class="text-sm sm:text-lg lg:text-2xl font-semibold text-red-900">
                                            {{ $connectionCount }}
                                        </p>
                                    </div>
                                    <p
                                        class="min-w-0 truncate text-xs lg:text-base font-semibold uppercase tracking-wide text-[#FFC107]">
                                        {{ __('Connections') }}
                                    </p>
                                    <div
                                        class="border border-red-900 absolute h-[50%] right-0 hidden sm:flex flex-col items-center">
                                    </div>
                                </div>
                                <div
                                    class="{{ $profileUser->isInstitution() ? 'p-0 sm:p-4 border-l border-red-900/30 sm:border-l-0' : 'col-span-2 sm:col-span-1 p-0 pt-2 sm:p-4 border-t border-red-900/30 sm:border-t-0' }} flex flex-col sm:flex-col gap-[6px] sm:gap-0 items-center justify-center min-w-0">
                                    <div class="flex items-center justify-center gap-1">
                                        <i
                                            class="fa-solid fa-pen-to-square text-red-900 text-xs sm:text-sm lg:text-xl"></i>
                                        <p class="text-sm sm:text-lg lg:text-2xl font-semibold text-red-900">
                                            {{ $activityPosts->count() }}
                                        </p>
                                    </div>
                                    <p
                                        class="min-w-0 text-center sm:text-left truncate text-xs lg:text-base font-semibold uppercase tracking-wide text-[#FFC107]">
                                        {{ $activityPosts->count() !== 1 ? __('Posts') : __('Post') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="!mt-3 grid gap-3 lg:grid-cols-3">
                    {{-- The institution account has no career profile. --}}
                    @unless ($profileUser->isInstitution())
                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2 max-w-full overflow-hidden">
                        <h3 class="text-base md:text-lg font-semibold tracking-wide text-gray-900">{{ __('Career Profile') }}
                        </h3>

                        @if ($canSeeCareerDetails)
                            <div class="mt-4 rounded-lg border border-gray-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Experience') }}
                                </p>
                                @if ($profileUser->profileExperiences->isNotEmpty())
                                    <div class="mt-3 space-y-3">
                                        @foreach ($profileUser->profileExperiences as $experience)
                                            <div class="rounded-md border border-gray-100 bg-gray-50 p-3">
                                                <p class="font-semibold text-gray-900">{{ $experience->title }}</p>
                                                <p class="text-sm text-gray-600">{{ $experience->organization }}</p>
                                                @if ($experience->start_date || $experience->end_date)
                                                    <p class="mt-1 text-sm text-gray-700">
                                                        {{ optional($experience->start_date)->format('M Y') ?? __('N/A') }}
                                                        —
                                                        {{ optional($experience->end_date)->format('M Y') ?? __('Present') }}
                                                    </p>
                                                @endif

                                                @if ($experience->description)
                                                    <p class="mt-1 text-sm text-gray-700">{{ $experience->description }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-2 text-sm text-gray-600">{{ __('No experience entries yet.') }}</p>
                                @endif
                            </div>

                            <div class="mt-5 rounded-lg border border-gray-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Education') }}
                                </p>
                                @if ($profileUser->profileEducations->isNotEmpty())
                                    <div class="mt-3 space-y-3">
                                        @foreach ($profileUser->profileEducations as $education)
                                            <div class="rounded-md border border-gray-100 bg-gray-50 p-3">
                                                <p class="font-semibold text-gray-900">{{ $education->school }}</p>
                                                <p class="text-sm text-gray-600">{{ $education->degree }}</p>
                                                @if ($education->start_date || $education->end_date)
                                                    <p class="mt-1 text-sm text-gray-700">
                                                        {{ optional($education->start_date)->format('M Y') ?? __('N/A') }}
                                                        —
                                                        {{ optional($education->end_date)->format('M Y') ?? __('Present') }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-2 text-sm text-gray-600">{{ __('No education entries yet.') }}</p>
                                @endif
                            </div>

                            <div class="mt-5 rounded-lg border border-gray-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('Skills') }}
                                </p>
                                @if (count($profileUser->parsedSkills()) > 0)
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($profileUser->parsedSkills() as $skill)
                                            <span
                                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                {{ $skill }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-2 text-sm text-gray-600">{{ __('No skills listed yet.') }}</p>
                                @endif
                            </div>
                        @else
                            <div class="mt-5 rounded-lg border border-[#FFC107] bg-[#FFC107] p-4 text-sm text-red-900">
                                {{ __('Experience is visible after verification.') }}
                            </div>
                            <div class="mt-3 rounded-lg border border-[#FFC107] bg-[#FFC107] p-4 text-sm text-red-900">
                                {{ __('Skills are visible after verification.') }}
                            </div>
                        @endif

                        <!-- @if ($showFullDetails)
                            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                                    {{ __('Full Contact Details') }}
                                </p>
                                <p class="mt-1 text-sm font-medium text-emerald-900">
                                    {{ __('Email: :email', ['email' => $profileUser->email]) }}
                                </p>
                            </div>
                        @else
                            <div class="mt-5 rounded-lg border border-[#FFC107] bg-[#FFC107] p-4 text-sm text-red-900">
                                {{ __('Additional profile details are hidden until your account is verified.') }}
                            </div>
                        @endif -->
                    </article>
                    @endunless

                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm max-w-full overflow-hidden {{ $profileUser->isInstitution() ? 'lg:col-span-3' : '' }}">
                        @php
                            $activityPostsForCarousel = $activityPosts
                                ->where('user_id', $profileUser->id)
                                ->values();

                            $activityPostsCounts = $activityPostsForCarousel
                                ->map(fn($p) => [
                                    'id' => $p->id,
                                    'like_count' => (int) ($p->likes_count ?? $p->like_count),
                                    'comment_count' => (int) ($p->comments_count ?? $p->comment_count),
                                ])
                                ->values();
                        @endphp

                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-base md:text-lg font-semibold tracking-wide text-gray-700">{{ __('Activity Posts') }}</h3>
                            <span class="shrink-0 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">
                                {{ $activityPostsForCarousel->count() }}
                            </span>
                        </div>

                        @if ($activityPostsForCarousel->isEmpty())
                            <div class="mt-4 rounded-lg border border-dashed border-gray-300 px-4 py-3 text-sm text-gray-600">
                                {{ __('No posts yet.') }}
                            </div>
                        @else
                            <div class="mt-4" x-data="postCardsCarousel(@js($activityPostsCounts), {{ $profileUser->isInstitution() ? 'true' : 'false' }})">
                                    <div class="relative overflow-hidden {{ $profileUser->isInstitution() ? '-mx-1.5' : '' }}" style="touch-action: pan-y;">
                                    <div class="flex transition-transform duration-300 ease-out min-w-0 js-post-cards-track"
                                        :style="`transform: translateX(-${activeIndex * 100}%);`">
                                        @foreach ($activityPostsForCarousel as $post)
                                            @php
                                                $visibilityConfig = match($post->visibility) {
                                                    'public'      => ['bg-green-50 text-green-700 ring-green-200', __('Public')],
                                                    'connections' => ['bg-blue-50 text-blue-700 ring-blue-200', __('Connections')],
                                                    default       => ['bg-gray-100 text-gray-600 ring-gray-200', __('Members')],
                                                };
                                                $profilePostApiUrl = $post->community
                                                    ? route('communities.posts.api', ['community' => $post->community, 'post' => $post])
                                                    : route('posts.api', ['post' => $post]);
                                                $profilePostCommentsUrl = $post->community
                                                    ? route('communities.posts.comments.index', ['community' => $post->community, 'post' => $post])
                                                    : route('posts.comments.index', ['post' => $post]);
                                                $carouselTrashUrl = route('posts.trash', $post);
                                                $carouselEditUrl  = route('posts.update', $post);
                                                $carouselReportUrl = route('posts.report', $post);
                                                $isCarouselAuthor = auth()->id() === $post->user_id;
                                                $canReportCarousel = ! $isCarouselAuthor && auth()->user()->isVerified();
                                            @endphp
                                            <div class="{{ $profileUser->isInstitution() ? 'w-full sm:w-1/2 lg:w-1/3 px-1.5' : 'w-full' }} shrink-0 min-w-0">
                                                <article
                                                    class="cursor-pointer overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-gray-300 hover:shadow-md"
                                                    @click="openPostModal($event, {{ $post->id }}, '{{ $profilePostApiUrl }}', '{{ $profilePostCommentsUrl }}')">

                                                    <!-- Top section -->
                                                    <div class="p-4 pb-3">
                                                        <div class="flex items-start justify-between gap-3">
                                                            <div class="flex items-center gap-3 min-w-0">
                                                                <img src="{{ $post->user->profileAvatarUrl() }}"
                                                                    alt="{{ $post->user->name }}"
                                                                    class="h-10 w-10 shrink-0 rounded-full border border-gray-200 object-cover"
                                                                    onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';">
                                                                <div class="min-w-0">
                                                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $post->user->name }}</p>
                                                                    <p class="truncate text-xs text-gray-500">
                                                                        <span class="truncate">{{ $post->community?->name ?? __('Post') }}</span>
                                                                        <span class="mx-1">·</span>
                                                                        <span>{{ $post->published_at?->diffForHumans() ?? $post->created_at->diffForHumans() }}</span>
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-center gap-1.5 shrink-0" @click.stop>
                                                            <span
                                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $visibilityConfig[0] }}">
                                                                {{ $visibilityConfig[1] }}
                                                            </span>
                                                            @if ($isCarouselAuthor || $canReportCarousel)
                                                                <div class="relative" x-data="{ menuOpen: false }">
                                                                    <button type="button" @click.stop="menuOpen = !menuOpen"
                                                                        class="flex h-7 w-7 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition"
                                                                        aria-label="Post options">
                                                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                                                            <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                                                        </svg>
                                                                    </button>
                                                                    <div x-show="menuOpen" @click.away="menuOpen = false"
                                                                        x-transition:enter="transition ease-out duration-100"
                                                                        x-transition:enter-start="opacity-0 scale-95"
                                                                        x-transition:enter-end="opacity-100 scale-100"
                                                                        x-transition:leave="transition ease-in duration-75"
                                                                        x-transition:leave-start="opacity-100 scale-100"
                                                                        x-transition:leave-end="opacity-0 scale-95"
                                                                        class="absolute right-0 top-full mt-1 z-30 w-44 rounded-xl border border-gray-200 bg-white shadow-lg py-1"
                                                                        style="display:none">
                                                                        @if ($isCarouselAuthor)
                                                                        <button type="button"
                                                                            @click.stop="menuOpen = false; $dispatch('open-edit-composer', @js(['postId' => $post->id, 'postType' => $post->post_type, 'title' => $post->title, 'body' => $post->body_markdown, 'visibility' => $post->visibility, 'communityId' => $post->community_id, 'editUrl' => $carouselEditUrl, 'flairs' => $post->flairs->pluck('id'), 'media' => $post->media->map(fn($m) => ['id' => $m->id, 'url' => $m->url]), 'event' => $post->event ? ['event_type' => $post->event->event_type, 'starts_at' => $post->event->starts_at?->format('Y-m-d\TH:i'), 'ends_at' => $post->event->ends_at?->format('Y-m-d\TH:i'), 'external_link' => $post->event->external_link, 'address' => $post->event->address, 'venue' => $post->event->venue] : null]))"
                                                                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                            </svg>
                                                                            Edit post
                                                                        </button>
                                                                        <form method="POST" action="{{ $carouselTrashUrl }}" @submit.prevent="if(confirm('Move this post to trash?')) $el.submit()">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" @click.stop="menuOpen = false"
                                                                                class="w-full flex items-center gap-2.5 px-3.5 py-2 text-sm text-red-700 hover:bg-red-50 transition">
                                                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                                </svg>
                                                                                Delete post
                                                                            </button>
                                                                        </form>
                                                                        @endif
                                                                        @if ($canReportCarousel)
                                                                        <button type="button"
                                                                            @click.stop="menuOpen = false; $dispatch('open-report-modal', @js(['reportUrl' => $carouselReportUrl]))"
                                                                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 2H21l-3 6 3 6h-8.5l-1-2H5a2 2 0 00-2 2z"/>
                                                                            </svg>
                                                                            Report post
                                                                        </button>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            </div>{{-- end pill+menu wrapper --}}
                                                        </div>{{-- end header row --}}

                                                        @if ($post->flairs->count() > 0)
                                                            <div class="mt-3 flex flex-wrap gap-1.5">
                                                                @foreach ($post->flairs->take(4) as $flair)
                                                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                                        style="background-color: {{ $flair->color ? $flair->color . '20' : '#f3f4f6' }}; color: {{ $flair->color ?? '#374151' }}; border: 1px solid {{ $flair->color ?? '#e5e7eb' }};">
                                                                        @if ($flair->icon)
                                                                            <span>{{ $flair->icon }}</span>
                                                                        @endif
                                                                        {{ $flair->name }}
                                                                    </span>
                                                                @endforeach
                                                                @if ($post->flairs->count() > 4)
                                                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                                                                        +{{ $post->flairs->count() - 4 }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Lower section -->
                                                    <div class="px-4 pb-4">
                                                        @if ($post->title)
                                                            <h4 class="text-base font-semibold text-gray-900 line-clamp-2">{{ $post->title }}</h4>
                                                        @endif

                                                        <p class="mt-2 text-sm leading-6 text-gray-700 line-clamp-3 break-words">
                                                            {{ \Illuminate\Support\Str::limit(strip_tags($post->body_html ?? $post->body_markdown), 220) }}
                                                        </p>

                                                            @if ($post->media->count() > 0)
                                                            <div class="mt-4 grid grid-cols-3 gap-2">
                                                                @foreach ($post->media->take(3) as $idx => $media)
                                                                    <div class="relative aspect-[4/3] overflow-hidden rounded-lg bg-gray-100 max-h-48">
                                                                        <img src="{{ $media->url }}" alt="{{ __('Post image') }}"
                                                                            class="h-full w-full object-cover max-h-48" />
                                                                        @if ($idx === 2 && $post->media->count() > 3)
                                                                            <div class="absolute inset-0 flex items-center justify-center bg-black/55 text-sm font-semibold text-white">
                                                                                +{{ $post->media->count() - 3 }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                        <div class="mt-4 flex flex-wrap items-center gap-2">
                                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                                </svg>
                                                                <span x-text="getLikeCount({{ $post->id }}, {{ (int) ($post->likes_count ?? $post->like_count) }})"></span>
                                                            </span>
                                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2l-4 4z" />
                                                                </svg>
                                                                <span x-text="getCommentCount({{ $post->id }}, {{ (int) ($post->comments_count ?? $post->comment_count) }})"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </article>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Carousel controls (same interaction style as modal media controls) -->
                                    <template x-if="count > 1">
                                        <div>
                                            <button type="button" @click="prev()"
                                                class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-gray-900/10 px-3 py-2 text-gray-900 hover:bg-gray-900/20"
                                                aria-label="{{ __('Previous post') }}">
                                                ‹
                                            </button>
                                            <button type="button" @click="next()"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-gray-900/10 px-3 py-2 text-gray-900 hover:bg-gray-900/20"
                                                aria-label="{{ __('Next post') }}">
                                                ›
                                            </button>
                                            <div
                                                class="absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full bg-gray-900/60 px-3 py-1 text-xs text-white">
                                                <span x-text="activeIndex + 1"></span>/<span x-text="count"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        @endif
                    </article>
                </section>

                @if ($isOwnProfile)
                    <div id="profile-media-modal"
                        class="fixed inset-0 z-50 hidden" role="dialog"
                        aria-modal="true" aria-labelledby="profile-media-modal-title" aria-hidden="true">
                        <button type="button" class="fixed inset-0 bg-black/60" data-profile-media-close
                            aria-label="{{ __('Close modal backdrop') }}"></button>

                        <div class="relative z-10 flex h-full w-full items-center justify-center px-4 py-6">
                            <div class="flex w-full max-w-3xl min-h-0 max-h-[calc(100vh-3rem)] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">
                                <div class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-200 bg-white px-6 py-4">
                                    <div>
                                        <h3 id="profile-media-modal-title" class="text-lg font-semibold text-gray-900">{{ __('Edit Profile Media') }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">
                                            {{ __('Update your avatar and banner.') }}
                                        </p>
                                    </div>

                                    <button type="button" data-profile-media-close
                                        class="rounded-md p-1 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                                        aria-label="{{ __('Close modal') }}">
                                        <span aria-hidden="true" class="text-3xl sm:text-4xl leading-none">&times;</span>
                                    </button>
                                </div>

                                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data"
                                    class="flex-1 min-h-0 space-y-6 overflow-y-auto px-6 pb-6">
                                @csrf
                                @method('patch')

                                <input type="hidden" name="redirect_to" value="profile_show" />
                                <input type="hidden" name="first_name" value="{{ old('first_name', $profileUser->first_name) }}" />
                                <input type="hidden" name="last_name" value="{{ old('last_name', $profileUser->last_name) }}" />
                                <input type="hidden" name="email" value="{{ old('email', $profileUser->email) }}" />
                                <input type="hidden" name="skills" value="{{ old('skills', $profileUser->skills) }}" />

                                @foreach ($profileUser->profileExperiences as $experienceIndex => $experience)
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][id]" value="{{ $experience->id }}" />
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][title]" value="{{ $experience->title }}" />
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][organization]" value="{{ $experience->organization }}" />
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][start_month]" value="{{ $experience->start_date?->format('Y-m') }}" />
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][end_month]" value="{{ $experience->end_date?->format('Y-m') }}" />
                                    <input type="hidden" name="experiences[{{ $experienceIndex }}][description]" value="{{ $experience->description }}" />
                                @endforeach

                                    <div class="mt-4 space-y-6">
                                        <div>
                                            <x-input-label for="banner" :value="__('Banner Photo')" />
                                            <img id="banner-preview" src="{{ $profileUser->profileBannerUrl() }}" alt="{{ __('Current banner preview') }}"
                                                onerror="this.onerror=null;this.src='{{ asset('images/default-banner.svg') }}';"
                                                class="mt-2 h-32 w-full rounded-lg border border-gray-200 object-cover sm:h-40" />
                                            <input id="banner" name="banner" type="file" accept="image/*"
                                                class="mt-3 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-red-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-red-700 file:cursor-pointer" />
                                            <p class="mt-1 text-xs text-gray-500">{{ __('Accepted: JPG, PNG, WEBP. Max 5MB.') }}</p>
                                            <x-input-error class="mt-2" :messages="$errors->get('banner')" />
                                        </div>

                                        <div>
                                            <x-input-label for="avatar" :value="__('Profile Photo (Avatar)')" />
                                            <img id="avatar-preview" src="{{ $profileUser->profileAvatarUrl() }}" alt="{{ __('Current avatar preview') }}"
                                                onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.svg') }}';"
                                                class="mt-2 h-28 w-28 rounded-full border border-gray-200 object-cover" />
                                            <input id="avatar" name="avatar" type="file" accept="image/*"
                                                class="mt-3 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-red-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-red-700 file:cursor-pointer" />
                                            <p class="mt-1 text-xs text-gray-500">{{ __('Accepted: JPG, PNG, WEBP. Max 3MB.') }}</p>
                                            <p id="avatar-crop-message" class="mt-2 text-xs text-emerald-700"></p>
                                            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                                        </div>
                                    </div>

                                <div id="avatar-crop-modal"
                                    class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/60 px-4 py-6" role="dialog"
                                    aria-modal="true" aria-labelledby="avatar-crop-title">
                                    <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-2xl">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h4 id="avatar-crop-title" class="text-base font-semibold text-gray-900">{{ __('Adjust profile photo') }}</h4>
                                                <p class="mt-1 text-xs text-gray-600">{{ __('Drag the photo to reposition, use the slider to zoom, then apply.') }}</p>
                                            </div>
                                            <button id="close-avatar-crop-modal" type="button"
                                                class="-mt-1 rounded-md p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                                                aria-label="{{ __('Close crop dialog') }}">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="mt-4 flex items-center justify-center">
                                            <canvas id="avatar-crop-canvas" width="320" height="320"
                                                class="h-56 w-56 touch-none select-none rounded-full border border-gray-200 bg-gray-100"></canvas>
                                        </div>

                                        <div class="mt-4">
                                            <label for="avatar-crop-zoom" class="text-xs font-medium text-gray-700">{{ __('Zoom') }}</label>
                                            <input id="avatar-crop-zoom" type="range" min="100" max="250" value="100"
                                                class="mt-1 w-full accent-red-900" />
                                        </div>

                                        <div class="mt-5 flex items-center justify-end gap-3">
                                            <button id="cancel-avatar-crop" type="button"
                                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                                {{ __('Cancel') }}
                                            </button>
                                            <button id="apply-avatar-crop" type="button"
                                                class="rounded-lg bg-red-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-800">
                                                {{ __('Apply Crop') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                    <div class="flex shrink-0 items-center justify-end gap-4 border-t border-gray-200 pt-4">
                                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function postCardsCarousel(initialPosts, multiItem = false) {
            const items = Array.isArray(initialPosts) ? initialPosts : [];
            const initialById = {};
            for (const item of items) {
                if (!item || typeof item.id !== 'number') continue;
                initialById[item.id] = {
                    like_count: Number(item.like_count) || 0,
                    comment_count: Number(item.comment_count) || 0,
                };
            }

            return {
                totalItems: items.length,
                multiItem: !!multiItem,
                windowWidth: window.innerWidth,
                activeIndex: 0,
                countsByPostId: initialById,

                // Cards shown per page: 3 desktop / 2 tablet / 1 mobile for the
                // institution's full-width activity; always 1 elsewhere.
                get perView() {
                    if (!this.multiItem) return 1;
                    if (this.windowWidth >= 1024) return 3;
                    if (this.windowWidth >= 640) return 2;
                    return 1;
                },

                // Number of pages (the pager/controls operate on pages).
                get count() {
                    return Math.max(1, Math.ceil(this.totalItems / this.perView));
                },

                init() {
                    window.addEventListener('post-like-count-changed', (event) => {
                        const postId = event?.detail?.postId ?? null;
                        const count = event?.detail?.count;
                        if (typeof postId !== 'number' || typeof count !== 'number') return;
                        if (!this.countsByPostId[postId]) this.countsByPostId[postId] = { like_count: 0, comment_count: 0 };
                        this.countsByPostId[postId].like_count = count;
                    });

                    window.addEventListener('post-comment-count-changed', (event) => {
                        const postId = Number(event?.detail?.postId);
                        const count = Number(event?.detail?.count);
                        if (!Number.isFinite(postId) || !Number.isFinite(count)) return;
                        if (!this.countsByPostId[postId]) this.countsByPostId[postId] = { like_count: 0, comment_count: 0 };
                        this.countsByPostId[postId].comment_count = count;
                    });
                    // Recompute pages/transform on resize (perView is breakpoint-based).
                    const update = () => {
                        this.windowWidth = window.innerWidth;
                        if (this.activeIndex > this.count - 1) {
                            this.activeIndex = this.count - 1;
                        }
                    };
                    window.addEventListener('resize', update);
                    setTimeout(update, 50);
                },

                getLikeCount(postId, fallback = 0) {
                    const entry = this.countsByPostId?.[postId];
                    return typeof entry?.like_count === 'number' ? entry.like_count : fallback;
                },

                getCommentCount(postId, fallback = 0) {
                    const entry = this.countsByPostId?.[postId];
                    return typeof entry?.comment_count === 'number' ? entry.comment_count : fallback;
                },

                next() {
                    if (this.count <= 1) return;
                    this.activeIndex = (this.activeIndex + 1) % this.count;
                },

                prev() {
                    if (this.count <= 1) return;
                    this.activeIndex = (this.activeIndex - 1 + this.count) % this.count;
                },

                openPostModal(event, postId, apiUrl, commentsUrl) {
                    event?.preventDefault?.();
                    window.dispatchEvent(new CustomEvent('post-modal-opened', {
                        detail: { postId, apiUrl, commentsUrl }
                    }));
                },
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            const profileMediaModal = document.getElementById('profile-media-modal');
            const openProfileMediaButtons = document.querySelectorAll('[data-profile-media-open]');
            const closeProfileMediaButtons = document.querySelectorAll('[data-profile-media-close]');
            const bannerInput = document.getElementById('banner');
            const bannerPreview = document.getElementById('banner-preview');
            const avatarInput = document.getElementById('avatar');
            const avatarPreview = document.getElementById('avatar-preview');
            const cropModal = document.getElementById('avatar-crop-modal');
            const closeCropModalBtn = document.getElementById('close-avatar-crop-modal');
            const cancelCropBtn = document.getElementById('cancel-avatar-crop');
            const cropZoom = document.getElementById('avatar-crop-zoom');
            const cropCanvas = document.getElementById('avatar-crop-canvas');
            const applyCropBtn = document.getElementById('apply-avatar-crop');
            const cropMessage = document.getElementById('avatar-crop-message');

            if (!profileMediaModal || !bannerInput || !bannerPreview || !avatarInput || !avatarPreview || !cropModal || !closeCropModalBtn || !cancelCropBtn || !cropZoom || !cropCanvas || !applyCropBtn || !cropMessage) {
                return;
            }

            const cropper = window.createAvatarCropper({ canvas: cropCanvas, zoomInput: cropZoom, outputSize: 512 });
            let sourceUrl = null;

            const syncBodyScroll = () => {
                const profileOpen = !profileMediaModal.classList.contains('hidden');
                const cropOpen = !cropModal.classList.contains('hidden');
                document.body.classList.toggle('overflow-hidden', profileOpen || cropOpen);
            };

            const openProfileMediaModal = () => {
                profileMediaModal.classList.remove('hidden');
                profileMediaModal.classList.add('flex');
                profileMediaModal.setAttribute('aria-hidden', 'false');
                syncBodyScroll();
            };

            const closeProfileMediaModal = () => {
                profileMediaModal.classList.add('hidden');
                profileMediaModal.classList.remove('flex');
                profileMediaModal.setAttribute('aria-hidden', 'true');
                if (!cropModal.classList.contains('hidden')) {
                    closeCropModal();
                    return;
                }
                syncBodyScroll();
            };

            const openCropModal = () => {
                cropModal.classList.remove('hidden');
                cropModal.classList.add('flex');
                syncBodyScroll();
            };

            const closeCropModal = () => {
                cropModal.classList.add('hidden');
                cropModal.classList.remove('flex');
                syncBodyScroll();
            };

            openProfileMediaButtons.forEach((button) => {
                button.addEventListener('click', openProfileMediaModal);
            });

            closeProfileMediaButtons.forEach((button) => {
                button.addEventListener('click', closeProfileMediaModal);
            });

            profileMediaModal.addEventListener('click', (event) => {
                if (event.target === profileMediaModal) {
                    closeProfileMediaModal();
                }
            });

            if (!profileMediaModal.classList.contains('hidden')) {
                syncBodyScroll();
            }

            @if ($errors->isNotEmpty())
                openProfileMediaModal();
            @endif

            bannerInput.addEventListener('change', () => {
                const file = bannerInput.files && bannerInput.files[0];

                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    if (event.target && typeof event.target.result === 'string') {
                        bannerPreview.src = event.target.result;
                    }
                };
                reader.readAsDataURL(file);
            });

            avatarInput.addEventListener('change', () => {
                const file = avatarInput.files && avatarInput.files[0];
                cropMessage.textContent = '';

                if (!file) {
                    return;
                }

                if (sourceUrl) {
                    URL.revokeObjectURL(sourceUrl);
                }

                sourceUrl = URL.createObjectURL(file);
                cropper.reset();
                cropper.setImage(sourceUrl);
                openCropModal();
            });

            cropModal.addEventListener('click', (event) => {
                if (event.target === cropModal) {
                    closeCropModal();
                }
            });

            closeCropModalBtn.addEventListener('click', closeCropModal);
            cancelCropBtn.addEventListener('click', closeCropModal);

            applyCropBtn.addEventListener('click', () => {
                cropper.toBlob((blob) => {
                    if (!blob) {
                        return;
                    }

                    const croppedFile = new File([blob], 'avatar-cropped.png', {
                        type: 'image/png'
                    });

                    const transfer = new DataTransfer();
                    transfer.items.add(croppedFile);
                    avatarInput.files = transfer.files;

                    avatarPreview.src = URL.createObjectURL(blob);
                    cropMessage.textContent = 'Crop applied. Save to upload this avatar.';
                    closeCropModal();
                });
            });
        });
    </script>

    @if ($isOwnProfile)
        {{-- Edit composer for the viewer's own posts (opened from the activity
             carousel via the open-edit-composer event). Edit-only: no create card.
             Same audience config as the dashboard so the audience picker works:
             General Hub → Everyone; program/batch → public/connections/community. --}}
        @php
            $joinedCommunitiesCollection = collect($joinedCommunities ?? []);
            $composerGeneralHubId = $joinedCommunitiesCollection->firstWhere('system_key', 'general-alumni-hub')->id ?? null;
            $defaultCommunityId = $composerGeneralHubId;
        @endphp
        @include('partials.post-composer', [
            'flairsByCommunity' => $flairsByCommunity ?? collect(),
            'defaultCommunityId' => $defaultCommunityId,
            'composerGeneralHubId' => $composerGeneralHubId,
            'composerEditOnly' => true,
        ])
        @include('partials.feed-scripts')
    @endif

    <x-post-detail-modal />
    <x-report-post-modal />

    <x-footer />
</x-app-layout>