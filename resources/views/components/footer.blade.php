<footer class="w-full bg-red-900 text-white mt-auto">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ asset('images/alumnihub-logo.png') }}" alt="AlumniHub Logo" class="w-10 h-10" />
                    <h2 class="font-bold text-lg">
                        <span class="text-white">Alumni</span><span class="text-[#FFC107]">Hub</span>
                    </h2>
                </div>

                <p class="text-sm text-white/80 leading-relaxed">
                    AlumniHub connects PUP ITECH graduates and students with opportunities, events, and their fellow
                    <em>Teknolohistas ng Bayan</em>.
                </p>

                <div class="flex gap-3 mt-5">
                    <a href="https://www.facebook.com/share/18amJzMWxS" target="_blank"
                        class="p-2 rounded-md bg-white/10 hover:bg-white/20 transition">
                        <!-- Facebook Icon -->
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22 12a10 10 0 1 0-11.5 9.9v-7h-2v-3h2V9.5c0-2 1.2-3.1 3-3.1.9 0 1.8.1 1.8.1v2h-1c-1 0-1.3.6-1.3 1.2V12h2.3l-.4 3h-1.9v7A10 10 0 0 0 22 12z" />
                        </svg>
                    </a>

                    <address class="p-2 rounded-md bg-white/10 hover:bg-white/20 transition not-italic">
                        <a href="mailto:alumnihub.2026@gmail.com" target="_blank" class="block">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z" />
                            </svg>
                        </a>
                    </address>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-base mb-4 text-[#FFC107]">Quick Links</h3>
                <ul class="space-y-2 text-sm text-white/80">
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="hover:text-white transition">Dashboard</a></li>
                        <li><a href="{{ route('connections.index') }}" class="hover:text-white transition">Connections</a>
                        </li>
                        <li><a href="{{ route('communities.index') }}" class="hover:text-white transition">Communities</a>
                        </li>
                        <li><a href="{{ route('profiles.show', auth()->user()) }}"
                                class="hover:text-white transition">Profile</a></li>
                    @else
                        <li><a href="{{ url('/') }}" class="hover:text-white transition">Get Started</a></li>
                        <li><a href="#prblem" class="hover:text-white transition">Challenges</a></li>
                        <li><a href="#features" class="hover:text-white transition">AlumniHub Features</a></li>
                        <li><a href="#alumnihub-team" class="hover:text-white transition">The Team</a></li>
                    @endauth
                </ul>
            </div>

            <div>
                <h3 class="font-semibold text-base mb-4 text-[#FFC107]">Resources</h3>
                <ul class="space-y-2 text-sm text-white/80">
                    <li><a href="#" class="hover:cursor-not-allowed transition" title="Coming Soon">Announcements</a>
                    </li>
                    <li><a href="#" class="hover:cursor-not-allowed transition" title="Coming Soon">FAQ</a></li>
                    <li><a href="#" class="hover:cursor-not-allowed transition" title="Coming Soon">Privacy Policy</a>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="font-semibold text-base mb-4 text-[#FFC107]">Stay Updated</h3>
                <p class="text-sm text-white/80 mb-4">
                    Subscribe to get updates about <em>Teknolohistas ng Bayan</em> events and announcements.
                </p>

                <form class="flex flex-col sm:flex-row gap-3" onsubmit="return false;" aria-disabled="true">
                    <input type="email" placeholder="Enter your email" disabled title="Coming Soon"
                        class="w-full px-4 py-2 rounded-md text-sm text-black bg-white/60 cursor-not-allowed focus:outline-none" />

                    <button type="submit" disabled title="Coming Soon"
                        class="bg-[#FFC107]/60 text-red-900/70 font-semibold px-5 py-2 rounded-md text-sm cursor-not-allowed whitespace-nowrap">
                        Subscribe
                    </button>
                </form>
                <!-- <p class="mt-2 text-xs italic text-white/60 flex justify-center">Coming soon</p> -->
            </div>

        </div>

        <div class="border-t border-white/20 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-white/70 text-center sm:text-left">
                © {{ date('Y') }} AlumniHub. All rights reserved.
            </p>

            <div class="flex gap-4 text-xs text-white/70">
                <a href="#" class="transition cursor-not-allowed">Terms</a>
                <a href="#" class="transition cursor-not-allowed">Privacy</a>
                <a href="#" class="transition cursor-not-allowed">Help</a>
            </div>
        </div>
    </div>
</footer>