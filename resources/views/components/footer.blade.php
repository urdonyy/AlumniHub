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
                    AlumniHub connects PUP ITECH graduates with opportunities, events, and fellow alumni.
                </p>

                <div class="flex gap-3 mt-5">
                    <a href="#" class="p-2 rounded-md bg-white/10 hover:bg-white/20 transition">
                        <!-- Facebook Icon -->
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M22 12a10 10 0 1 0-11.5 9.9v-7h-2v-3h2V9.5c0-2 1.2-3.1 3-3.1.9 0 1.8.1 1.8.1v2h-1c-1 0-1.3.6-1.3 1.2V12h2.3l-.4 3h-1.9v7A10 10 0 0 0 22 12z" />
                        </svg>
                    </a>

                    <a href="#" class="p-2 rounded-md bg-white/10 hover:bg-white/20 transition">
                        <!-- LinkedIn Icon -->
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.4 20.4h-3.6v-5.6c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.7H9.3V9h3.4v1.6h.1c.5-.9 1.6-1.8 3.3-1.8 3.5 0 4.2 2.3 4.2 5.3v6.3zM5.3 7.4c-1.2 0-2.1-1-2.1-2.1S4.1 3.2 5.3 3.2s2.1 1 2.1 2.1-1 2.1-2.1 2.1zM7.1 20.4H3.5V9h3.6v11.4z" />
                        </svg>
                    </a>

                    <a href="#" class="p-2 rounded-md bg-white/10 hover:bg-white/20 transition">
                        <!-- Mail Icon -->
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z" />
                        </svg>
                    </a>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-base mb-4 text-[#FFC107]">Quick Links</h3>
                <ul class="space-y-2 text-sm text-white/80">
                    <li><a href="{{ url('/') }}" class="hover:text-white transition">Home</a></li>
                    <li><a href="#" class="hover:text-white transition">About</a></li>
                    <li><a href="#" class="hover:text-white transition">Events</a></li>
                    <li><a href="#" class="hover:text-white transition">Contact</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-semibold text-base mb-4 text-[#FFC107]">Resources</h3>
                <ul class="space-y-2 text-sm text-white/80">
                    <li><a href="#" class="hover:text-white transition">Job Opportunities</a></li>
                    <li><a href="#" class="hover:text-white transition">Announcements</a></li>
                    <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                    <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-semibold text-base mb-4 text-[#FFC107]">Stay Updated</h3>
                <p class="text-sm text-white/80 mb-4">
                    Subscribe to get updates about alumni events and announcements.
                </p>

                <form class="flex flex-col sm:flex-row gap-3">
                    <input type="email" placeholder="Enter your email"
                        class="w-full px-4 py-2 rounded-md text-sm text-black focus:outline-none focus:ring-2 focus:ring-[#FFC107]" />

                    <button type="submit"
                        class="bg-[#FFC107] text-red-900 font-semibold px-5 py-2 rounded-md text-sm hover:bg-yellow-400 transition whitespace-nowrap">
                        Subscribe
                    </button>
                </form>
            </div>

        </div>

        <div class="border-t border-white/20 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-white/70 text-center sm:text-left">
                © {{ date('Y') }} AlumniHub. All rights reserved.
            </p>

            <div class="flex gap-4 text-xs text-white/70">
                <a href="#" class="hover:text-white transition">Terms</a>
                <a href="#" class="hover:text-white transition">Privacy</a>
                <a href="#" class="hover:text-white transition">Help</a>
            </div>
        </div>
    </div>
</footer>