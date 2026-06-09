    <script>
        function postComposer(flairsByCommunity, defaultCommunityId, config = {}) {
            return {
                open: false,
                editMode: false,
                editUrl: null,
                communityId: defaultCommunityId ? String(defaultCommunityId) : '',
                flairsByCommunity,
                // Audience config: which visibilities the picker may offer, and whether the
                // community is locked (community pages) vs freely chosen (dashboard).
                allowedVisibilities: config.allowedVisibilities || ['public', 'connections', 'members'],
                lockedCommunityId: config.lockedCommunityId ?? null,
                generalHubId: config.generalHubId ?? null,
                visibility: (config.allowedVisibilities && !config.allowedVisibilities.includes('members'))
                    ? config.allowedVisibilities[0]
                    : 'members',
                audienceOpen: false,
                selectedFlairs: [],
                flairsExpanded: false,
                flairError: false,
                communityError: false,
                eventDateError: false,
                isSubmitting: false,

                // Edit mode: existing post media + ids the user removed
                existingMedia: [],
                removedMediaIds: [],

                // Post type + shared title/body
                postType: 'text', // text | media | event
                titleValue: '',
                bodyValue: '',

                // Event fields
                eventType: 'online', // online | in_person
                startDate: '',
                startTime: '',
                hasEndDate: false,
                endDate: '',
                endTime: '',
                externalLink: '',
                address: '',
                venue: '',
                autoInvite: false,

                get isText() { return this.postType === 'text'; },
                get isMedia() { return this.postType === 'media'; },
                get isEvent() { return this.postType === 'event'; },
                get visibleExistingMedia() { return this.existingMedia.filter(m => !this.removedMediaIds.includes(m.id)); },

                get isGeneralHubSelected() {
                    return this.generalHubId != null && String(this.communityId) === String(this.generalHubId);
                },
                // General Alumni Hub auto-joins everyone, so its "Community" audience reaches the same
                // people as "Public" — drop the redundant "Public" option when it's selected.
                get effectiveVisibilities() {
                    return this.isGeneralHubSelected
                        ? this.allowedVisibilities.filter(v => v !== 'public')
                        : this.allowedVisibilities;
                },
                coerceAudienceForCommunity() {
                    if (this.visibility !== 'connections' && !this.effectiveVisibilities.includes(this.visibility)) {
                        this.visibility = this.effectiveVisibilities.includes('members')
                            ? 'members'
                            : (this.effectiveVisibilities[0] || 'members');
                    }
                },

                get startsAtValue() {
                    return this.startDate && this.startTime ? `${this.startDate} ${this.startTime}` : '';
                },
                get endsAtValue() {
                    return this.hasEndDate && this.endDate && this.endTime ? `${this.endDate} ${this.endTime}` : '';
                },

                setPostType(type) {
                    this.postType = type;
                    if (type === 'event' && this.visibility === 'public') {
                        this.visibility = 'members';
                    }
                },

                init() {
                    // Keep the audience valid when the selected community changes (e.g. switching
                    // to General Hub hides "Public", so coerce a stale "public" to "members").
                    this.$watch('communityId', () => this.coerceAudienceForCommunity());

                    // Lock body scroll while the composer is open (shared with the
                    // post-view modal so the modal→edit hand-off never snaps). Falls
                    // back to a no-op if the shared lock isn't on the page.
                    this.$watch('open', (val) => {
                        if (val) window.__bodyScrollLock?.lock();
                        else window.__bodyScrollLock?.unlock();
                    });

                    window.addEventListener('open-edit-composer', (e) => {
                        const d = e.detail;
                        this.editMode    = true;
                        this.editUrl     = d.editUrl;
                        this.postType    = d.postType ?? 'text';
                        this.titleValue  = d.title ?? '';
                        this.bodyValue   = d.body ?? '';
                        this.visibility  = d.visibility ?? 'members';
                        this.communityId = d.communityId ? String(d.communityId) : '';
                        this.selectedFlairs  = Array.isArray(d.flairs) ? d.flairs.map(Number) : [];
                        this.existingMedia   = Array.isArray(d.media) ? d.media : [];
                        this.removedMediaIds = [];
                        this.resetImageUpload();

                        if (d.event) {
                            this.eventType  = d.event.event_type ?? 'online';
                            const sa        = d.event.starts_at ? d.event.starts_at.split('T') : ['', ''];
                            const ea        = d.event.ends_at   ? d.event.ends_at.split('T')   : ['', ''];
                            this.startDate  = sa[0] ?? '';
                            this.startTime  = sa[1] ?? '';
                            this.hasEndDate = !!d.event.ends_at;
                            this.endDate    = ea[0] ?? '';
                            this.endTime    = ea[1] ?? '';
                            this.externalLink = d.event.external_link ?? '';
                            this.address    = d.event.address ?? '';
                            this.venue      = d.event.venue   ?? '';
                        }

                        this.open = true;
                    });
                },

                closeAndReset() {
                    this.open            = false;
                    this.editMode        = false;
                    this.editUrl         = null;
                    this.existingMedia   = [];
                    this.removedMediaIds = [];
                    this.selectedFlairs  = [];
                    this.resetImageUpload();
                },

                resetImageUpload() {
                    const input = document.getElementById('imageUploadInput');
                    const container = document.getElementById('imagePreviewContainer');
                    const single = document.getElementById('singleImagePreview');
                    const grid = document.getElementById('multiImageGrid');
                    if (input) input.value = '';
                    if (container) container.classList.add('hidden');
                    if (single) { single.src = ''; single.classList.add('hidden'); }
                    if (grid) { grid.innerHTML = ''; grid.className = 'hidden gap-0.5'; }
                },

                get isConnectionsOnly() {
                    return this.visibility === 'connections';
                },

                get filteredFlairs() {
                    const global = this.flairsByCommunity['global'] || [];
                    const community = this.communityId && !this.isConnectionsOnly
                        ? (this.flairsByCommunity[String(this.communityId)] || [])
                        : [];
                    const seen = new Set();
                    return [...global, ...community].filter(f => {
                        if (seen.has(f.id)) return false;
                        seen.add(f.id);
                        return true;
                    });
                },

                get visibleFlairs() {
                    return this.flairsExpanded ? this.filteredFlairs : this.filteredFlairs.slice(0, 4);
                },

                toggleFlair(id) {
                    const idx = this.selectedFlairs.indexOf(id);
                    if (idx >= 0) {
                        this.selectedFlairs.splice(idx, 1);
                    } else if (this.selectedFlairs.length < 3) {
                        this.selectedFlairs.push(id);
                    }
                    this.flairError = false;
                },

                canSelectFlair(id) {
                    return this.selectedFlairs.includes(id) || this.selectedFlairs.length < 3;
                },

                onVisibilityChange(newVisibility) {
                    this.visibility = newVisibility;
                    this.audienceOpen = false;
                    this.communityError = false;
                    // Don't wipe the selected community/flairs when switching to
                    // connections — the hidden inputs already omit them for
                    // connections, and preserving them lets the user switch back
                    // to Public/Community without the dropdown going blank.
                },

                handleImageUpload(event) {
                    const files = event.target.files;
                    const container = document.getElementById('imagePreviewContainer');
                    const single = document.getElementById('singleImagePreview');
                    const grid = document.getElementById('multiImageGrid');
                    const removeBtn = document.getElementById('removeImageBtn');

                    if (!files || files.length === 0) {
                        container.classList.add('hidden');
                        return;
                    }

                    container.classList.remove('hidden');

                    if (files.length === 1) {
                        grid.classList.add('hidden');
                        single.classList.remove('hidden');
                        const reader = new FileReader();
                        reader.onload = e => { single.src = e.target.result; };
                        reader.readAsDataURL(files[0]);
                    } else {
                        single.classList.add('hidden');
                        grid.classList.remove('hidden');
                        const show = Math.min(files.length, 4);
                        grid.className = `grid gap-0.5 ${show === 2 ? 'grid-cols-2' : show === 3 ? 'grid-cols-3' : 'grid-cols-2'}`;
                        grid.innerHTML = '';
                        Array.from(files).slice(0, 4).forEach((file, i) => {
                            const wrap = document.createElement('div');
                            wrap.className = 'relative';
                            const img = document.createElement('img');
                            img.className = 'w-full h-32 object-cover';
                            const reader = new FileReader();
                            reader.onload = e => { img.src = e.target.result; };
                            reader.readAsDataURL(file);
                            wrap.appendChild(img);
                            if (i === 3 && files.length > 4) {
                                const overlay = document.createElement('div');
                                overlay.className = 'absolute inset-0 flex items-center justify-center bg-black/50 text-white font-semibold text-lg';
                                overlay.textContent = `+${files.length - 4}`;
                                wrap.appendChild(overlay);
                            }
                            grid.appendChild(wrap);
                        });
                    }

                    removeBtn.onclick = () => {
                        container.classList.add('hidden');
                        single.src = '';
                        grid.innerHTML = '';
                        grid.className = 'hidden gap-0.5';
                        event.target.value = '';
                    };
                },

                submitPost(form) {
                    if (this.isSubmitting) return;

                    if (this.isEvent) {
                        const starts = this.startsAtValue ? new Date(this.startsAtValue) : null;
                        if (!starts || starts <= new Date()) {
                            this.eventDateError = true;
                            return;
                        }
                        this.eventDateError = false;
                    }

                    // Non-connections posts must have a community selected, or the
                    // server silently rejects them. Block + flag instead of failing.
                    if (!this.editMode && !this.isConnectionsOnly && !this.communityId) {
                        this.communityError = true;
                        return;
                    }
                    this.communityError = false;

                    // Flairs aren't edited via the composer, so skip the requirement in edit mode.
                    if (!this.editMode && !this.isEvent && this.filteredFlairs.length > 0 && !this.isConnectionsOnly && this.selectedFlairs.length === 0) {
                        this.flairError = true;
                        return;
                    }
                    this.flairError = false;
                    this.isSubmitting = true;
                    form.submit();
                }
            };
        }

        function feedManager() {
            return {
                showModal: false,
                selectedPostId: null,
                apiUrl: null,
                commentsUrl: null,

                openPostModal(event, postId, apiUrl, commentsUrl) {
                    event?.preventDefault();
                    this.selectedPostId = postId;
                    this.apiUrl = apiUrl;
                    this.commentsUrl = commentsUrl;
                    this.showModal = true;
                    window.dispatchEvent(new CustomEvent('post-modal-opened', {
                        detail: { postId, apiUrl, commentsUrl }
                    }));
                },

                closeModal() {
                    this.showModal = false;
                    this.selectedPostId = null;
                    this.apiUrl = null;
                    this.commentsUrl = null;
                }
            };
        }

        function postCard(postId, initialLikeCount, initialCommentCount, apiUrl, likeUrl, isInitiallyLiked = false, meta = {}) {
            return {
                postId,
                likeCount: initialLikeCount,
                commentCount: initialCommentCount,
                apiUrl,
                likeUrl,
                isLiked: isInitiallyLiked,
                isLikingLoading: false,
                isBodyExpanded: false,
                isBodyOverflowing: false,

                // Editable fields kept in sync with the detail modal (post-updated event).
                visibility: meta.visibility ?? 'members',
                title: meta.title ?? '',
                bodyText: meta.body ?? '',

                get visibilityLabel() {
                    return { public: 'Public', connections: 'Connections', members: 'Members' }[this.visibility] || 'Members';
                },
                get visibilityClass() {
                    return {
                        public: 'bg-green-50 text-green-700 ring-green-200',
                        connections: 'bg-blue-50 text-blue-700 ring-blue-200',
                        members: 'bg-gray-100 text-gray-600 ring-gray-200',
                    }[this.visibility] || 'bg-gray-100 text-gray-600 ring-gray-200';
                },

                init() {
                    window.addEventListener('post-updated', (event) => {
                        if (Number(event?.detail?.postId) !== Number(this.postId)) return;
                        const d = event.detail;
                        if (d.visibility) this.visibility = d.visibility;
                        if ('title' in d) this.title = d.title ?? '';
                        if ('body' in d) { this.bodyText = d.body ?? ''; this.refreshBodyOverflow(); }
                    });

                    window.addEventListener('post-comment-count-changed', (event) => {
                        const postId = Number(event?.detail?.postId);
                        const count = Number(event?.detail?.count);
                        if (!Number.isFinite(postId) || !Number.isFinite(count)) return;
                        if (postId !== Number(this.postId)) return;
                        this.commentCount = count;
                    });

                    // Keep the feed card's like state in sync with the detail modal
                    // (both when liking inside it and when it loads the true count).
                    window.addEventListener('post-like-count-changed', (event) => {
                        if (Number(event?.detail?.postId) !== Number(this.postId)) return;
                        const count = Number(event?.detail?.count);
                        if (Number.isFinite(count)) this.likeCount = count;
                        if (typeof event?.detail?.liked === 'boolean') this.isLiked = event.detail.liked;
                    });

                    this.refreshBodyOverflow();
                },

                refreshBodyOverflow() {
                    this.isBodyOverflowing = false;
                    if (this.isBodyExpanded) return;
                    this.$nextTick(() => {
                        const el = this.$refs?.postBody;
                        if (!el) return;
                        this.isBodyOverflowing = el.scrollHeight > el.clientHeight + 2;
                    });
                },

                toggleBody() {
                    this.isBodyExpanded = !this.isBodyExpanded;
                    if (!this.isBodyExpanded) this.refreshBodyOverflow();
                },

                openPostModal(event) {
                    // Both article and comment button call this; buttons use @click.stop so
                    // only non-button areas bubble up through the article click handler.
                    // Community-less posts (connections-only) have no detail API route.
                    if (!this.apiUrl) return;
                    const commentsUrl = this.apiUrl.replace('/api', '/comments');
                    window.dispatchEvent(new CustomEvent('post-modal-opened', {
                        detail: { postId: this.postId, apiUrl: this.apiUrl, commentsUrl }
                    }));
                },

                toggleLike() {
                    if (this.isLikingLoading || !this.likeUrl) return;
                    this.isLikingLoading = true;

                    fetch(this.likeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        },
                    })
                        .then(response => {
                            if (!response.ok) throw new Error('Failed to like post');
                            return response.json();
                        })
                        .then(data => {
                            this.isLiked = data.liked;
                            this.likeCount = data.like_count;
                            this.isLikingLoading = false;
                            window.dispatchEvent(new CustomEvent('post-like-count-changed', {
                                detail: { postId: this.postId, count: this.likeCount, liked: this.isLiked }
                            }));
                        })
                        .catch(err => {
                            console.error('Error liking post:', err);
                            this.isLikingLoading = false;
                        });
                }
            };
        }

        function feedController(availableFlairs, selectedIds, initialHasMore, initialPage, feedUrl = '/feed/posts') {
            return {
                feedUrl,
                flairs: availableFlairs,
                selected: selectedIds,
                expanded: false,
                loading: false,       // filter change (replace) in progress
                loadingMore: false,   // infinite-scroll append in progress
                page: initialPage,    // last page loaded into the feed
                hasMore: initialHasMore,
                observer: null,

                get visibleFlairs() {
                    return this.expanded ? this.flairs : this.flairs.slice(0, 8);
                },
                get reachedEnd() {
                    return !this.hasMore && !this.loadingMore && !this.loading;
                },
                isSelected(id) { return this.selected.includes(id); },
                canSelect(id) { return this.isSelected(id) || this.selected.length < 3; },

                buildQuery(page) {
                    const params = new URLSearchParams();
                    this.selected.forEach(id => params.append('flairs[]', id));
                    if (page > 1) params.set('page', page);
                    return params.toString();
                },

                toggle(id) {
                    if (this.isSelected(id)) {
                        this.selected = this.selected.filter(s => s !== id);
                    } else if (this.selected.length < 3) {
                        this.selected = [...this.selected, id];
                    } else {
                        return;
                    }
                    this.applyFilter();
                },
                clearAll() {
                    this.selected = [];
                    this.applyFilter();
                },

                // Flair change: reset to page 1 and replace the feed.
                applyFilter() {
                    const url = new URL(window.location.href);
                    url.search = '';
                    this.selected.forEach(id => url.searchParams.append('flairs[]', id));
                    history.pushState({}, '', url.toString());

                    this.loading = true;
                    this.page = 1;
                    fetch(this.feedUrl + '?' + this.buildQuery(1), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.json())
                        .then(data => {
                            document.getElementById('feed-posts-container').innerHTML = data.html;
                            this.hasMore = data.hasMore;
                            this.loading = false;
                            this.$nextTick(() => this.fillViewport());
                        })
                        .catch(() => { this.loading = false; });
                },

                // Infinite scroll: fetch the next page and append.
                loadMore() {
                    if (this.loadingMore || this.loading || !this.hasMore) return;
                    this.loadingMore = true;
                    const next = this.page + 1;
                    fetch(this.feedUrl + '?' + this.buildQuery(next), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.json())
                        .then(data => {
                            document.getElementById('feed-posts-container').insertAdjacentHTML('beforeend', data.html);
                            this.page = next;
                            this.hasMore = data.hasMore;
                            this.loadingMore = false;
                            this.$nextTick(() => this.fillViewport());
                        })
                        .catch(() => { this.loadingMore = false; });
                },

                // Keep loading while the sentinel is still on-screen (short feeds).
                fillViewport() {
                    const s = this.$refs.sentinel;
                    if (!s || !this.hasMore) return;
                    if (s.getBoundingClientRect().top < window.innerHeight) this.loadMore();
                },

                initScroll() {
                    this.observer = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting) this.loadMore();
                    }, { rootMargin: '400px' });
                    this.observer.observe(this.$refs.sentinel);
                }
            };
        }
    </script>
