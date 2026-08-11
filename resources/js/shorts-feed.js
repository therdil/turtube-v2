const shortsGrid = document.querySelector('[data-shorts-grid]');

if (shortsGrid) {
    const sentinel = document.querySelector('[data-shorts-sentinel]');
    let nextPageUrl = shortsGrid.dataset.nextPageUrl || '';
    let isLoading = false;

    const createShortCard = (short) => {
        const link = document.createElement('a');
        link.href = short.url;
        link.className = 'group overflow-hidden rounded-3xl border border-gray-800 bg-gray-900 transition hover:-translate-y-1 hover:border-red-500';

        const media = document.createElement('div');
        media.className = 'relative aspect-[9/16] bg-black';
        if (short.thumbnail_url) {
            const image = document.createElement('img');
            image.src = short.thumbnail_url;
            image.alt = short.title;
            image.loading = 'lazy';
            image.decoding = 'async';
            image.className = 'h-full w-full object-cover transition duration-300 group-hover:scale-105';
            media.append(image);
        }

        const details = document.createElement('div');
        details.className = 'absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-5 pt-20';
        const title = document.createElement('h2');
        title.className = 'line-clamp-2 font-bold text-white';
        title.textContent = short.title;
        const meta = document.createElement('p');
        meta.className = 'mt-2 text-sm text-gray-300';
        meta.textContent = `${short.channel} · ${short.views}`;
        details.append(title, meta);
        media.append(details);
        link.append(media);

        return link;
    };

    const loadMore = async () => {
        if (!nextPageUrl || isLoading) return;
        isLoading = true;
        sentinel?.classList.remove('hidden');

        try {
            const response = await fetch(nextPageUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) return;
            const payload = await response.json();
            payload.data.forEach((short) => shortsGrid.append(createShortCard(short)));
            nextPageUrl = payload.next_page_url || '';
            shortsGrid.dataset.nextPageUrl = nextPageUrl;
        } catch {
            // Existing cards remain usable when the next page cannot be loaded.
        } finally {
            isLoading = false;
            if (!nextPageUrl) sentinel?.classList.add('hidden');
        }
    };

    if (sentinel && 'IntersectionObserver' in window) {
        new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) loadMore();
        }, { rootMargin: '600px 0px' }).observe(sentinel);
    }
}

const shortsFeed = document.querySelector('[data-shorts-player-feed]');

if (shortsFeed) {
    const items = Array.from(shortsFeed.querySelectorAll('[data-short-item]'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const isAuthenticated = shortsFeed.dataset.authenticated === 'true';
    const loginUrl = shortsFeed.dataset.loginUrl || '/login';
    let wheelLocked = false;
    let touchStartY = 0;

    const setText = (item, selector, value) => {
        item.querySelectorAll(selector).forEach((element) => {
            element.textContent = value;
        });
    };

    const setActionState = (item, selector, active) => {
        item.querySelectorAll(selector).forEach((button) => {
            button.dataset.active = String(active);
            button.classList.toggle('is-active', active);
        });
    };

    const showMessage = (message) => {
        const toast = document.createElement('div');
        toast.className = 'fixed inset-x-4 bottom-6 z-[100] mx-auto w-fit max-w-[calc(100vw-2rem)] rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3 text-sm font-medium text-white shadow-2xl';
        toast.textContent = message;
        document.body.append(toast);
        window.setTimeout(() => toast.remove(), 2600);
    };

    const requireAuth = () => {
        if (isAuthenticated) return true;
        window.location.assign(loginUrl);
        return false;
    };

    const post = async (url, body = null) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                ...(body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
            },
            body: body instanceof FormData ? body : (body ? JSON.stringify(body) : null),
        });

        if (response.status === 401 || response.status === 419) {
            window.location.assign(loginUrl);
            throw new Error('Authentication required');
        }

        if (!response.ok) {
            throw new Error('Request failed');
        }

        return response.json();
    };

    const setPlayingState = (item, playing) => {
        const pausedOverlay = item.querySelector('[data-short-paused]');
        const pauseIcon = item.querySelector('[data-short-pause-icon]');
        const playIcon = item.querySelector('[data-short-play-icon]');
        const button = item.querySelector('[data-short-play]');

        pausedOverlay?.classList.toggle('hidden', playing);
        pausedOverlay?.classList.toggle('flex', !playing);
        pauseIcon?.classList.toggle('hidden', !playing);
        playIcon?.classList.toggle('hidden', playing);
        button?.setAttribute('aria-label', playing ? 'Videoyu duraklat' : 'Videoyu oynat');
    };

    const setMutedState = (item, muted) => {
        item.querySelector('[data-short-muted-icon]')?.classList.toggle('hidden', !muted);
        item.querySelector('[data-short-unmuted-icon]')?.classList.toggle('hidden', muted);
        item.querySelector('[data-short-mute]')?.setAttribute('aria-label', muted ? 'Sesi aç' : 'Sesi kapat');
    };

    const activeIndex = () => {
        const feedBounds = shortsFeed.getBoundingClientRect();
        return items.reduce((closestIndex, item, index) => {
            const currentDistance = Math.abs(item.getBoundingClientRect().top - feedBounds.top);
            const closestDistance = Math.abs(items[closestIndex].getBoundingClientRect().top - feedBounds.top);
            return currentDistance < closestDistance ? index : closestIndex;
        }, 0);
    };

    const moveTo = (index) => {
        items[Math.max(0, Math.min(index, items.length - 1))]
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    const syncPlayback = () => {
        const current = activeIndex();
        items.forEach((item, index) => {
            const player = item.querySelector('[data-short-video]');
            if (!player) return;
            if (index === current) {
                player.play().catch(() => setPlayingState(item, false));
            } else {
                player.pause();
            }
        });
    };

    const bindCommentReaction = (item, button) => {
        button.addEventListener('click', async () => {
            if (!requireAuth() || button.disabled) return;
            button.disabled = true;

            try {
                const data = await post(button.dataset.url, { reaction: button.dataset.commentReaction });
                const comment = button.closest('article');
                comment.querySelector('[data-comment-likes]').textContent = data.likes_count;
                comment.querySelector('[data-comment-dislikes]').textContent = data.dislikes_count;
                comment.querySelectorAll('[data-comment-reaction]').forEach((reactionButton) => {
                    const active = reactionButton.dataset.commentReaction === data.reaction;
                    reactionButton.classList.toggle('bg-red-600/15', active);
                    reactionButton.classList.toggle('text-red-200', active);
                    reactionButton.setAttribute('aria-pressed', String(active));
                });
            } catch {
                showMessage('Yorum tepkisi kaydedilemedi.');
            } finally {
                button.disabled = false;
            }
        });
    };

    const appendComment = (item, comment) => {
        const list = item.querySelector('[data-short-comments-list]');
        if (!list) return;

        const empty = list.querySelector('[data-short-comments-empty]');
        empty?.remove();

        const article = document.createElement('article');
        article.className = 'rounded-2xl bg-zinc-900/80 p-3';
        article.innerHTML = '<div class="flex gap-3"><a data-channel-link class="shrink-0"><span data-avatar-fallback class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-700 text-xs font-bold text-white"></span></a><div class="min-w-0 flex-1"><p class="flex min-w-0 items-center gap-1 text-sm font-semibold text-white"><a data-author class="truncate transition hover:text-red-300"></a><span data-verified class="hidden shrink-0 text-sky-400">✓</span><span class="ml-1 shrink-0 text-xs font-normal text-zinc-500" data-time></span></p><p class="mt-1 break-words whitespace-pre-line text-sm leading-5 text-zinc-300" data-body></p><div class="mt-2 flex gap-2"><button type="button" data-comment-reaction="like" aria-label="Yorumu beğen" aria-pressed="false" class="rounded-lg px-2 py-1 text-xs text-zinc-400 transition hover:bg-zinc-800 hover:text-white">♡ <span data-comment-likes>0</span></button><button type="button" data-comment-reaction="dislike" aria-label="Yorumu beğenme" aria-pressed="false" class="rounded-lg px-2 py-1 text-xs text-zinc-400 transition hover:bg-zinc-800 hover:text-white">👎 <span data-comment-dislikes>0</span></button></div></div></div>';

        const channelLink = article.querySelector('[data-channel-link]');
        channelLink.href = comment.channel_url;
        channelLink.setAttribute('aria-label', `${comment.author} kanalına git`);
        article.querySelector('[data-author]').href = comment.channel_url;
        article.querySelector('[data-author]').textContent = comment.author;
        article.querySelector('[data-time]').textContent = comment.created_at;
        article.querySelector('[data-body]').textContent = comment.body;

        if (comment.avatar_url) {
            const avatar = document.createElement('img');
            avatar.src = comment.avatar_url;
            avatar.alt = '';
            avatar.className = 'h-9 w-9 rounded-full object-cover';
            article.querySelector('[data-avatar-fallback]').replaceWith(avatar);
        } else {
            article.querySelector('[data-avatar-fallback]').textContent = comment.initial;
        }

        article.querySelector('[data-verified]').classList.toggle('hidden', !comment.is_verified);
        article.querySelectorAll('[data-comment-reaction]').forEach((button) => {
            button.dataset.url = comment.reaction_url;
            bindCommentReaction(item, button);
        });
        list.prepend(article);
    };

    items.forEach((item, index) => {
        const video = item.querySelector('[data-short-video]');
        const progress = item.querySelector('[data-short-progress]');
        const stage = item.querySelector('[data-short-stage]');

        if (!video) return;

        video.addEventListener('play', () => setPlayingState(item, true));
        video.addEventListener('pause', () => setPlayingState(item, false));
        video.addEventListener('volumechange', () => setMutedState(item, video.muted || video.volume === 0));
        video.addEventListener('timeupdate', () => {
            if (progress && Number.isFinite(video.duration) && video.duration > 0) {
                progress.value = String((video.currentTime / video.duration) * 100);
            }
        });
        video.addEventListener('ended', () => moveTo(index + 1));
        video.addEventListener('click', () => {
            if (video.paused) video.play().catch(() => {}); else video.pause();
        });

        item.querySelector('[data-short-play]')?.addEventListener('click', () => {
            if (video.paused) video.play().catch(() => {}); else video.pause();
        });
        item.querySelector('[data-short-mute]')?.addEventListener('click', () => {
            video.muted = !video.muted;
            if (!video.muted) video.volume = Math.max(video.volume, 0.65);
        });
        progress?.addEventListener('input', () => {
            if (Number.isFinite(video.duration)) video.currentTime = (Number(progress.value) / 100) * video.duration;
        });
        item.querySelector('[data-short-fullscreen]')?.addEventListener('click', () => {
            if (document.fullscreenElement) document.exitFullscreen?.(); else stage?.requestFullscreen?.();
        });

        item.querySelectorAll('[data-short-like]').forEach((button) => button.addEventListener('click', async () => {
            if (!requireAuth() || button.disabled) return;
            button.disabled = true;
            try {
                const data = await post(button.dataset.url);
                setActionState(item, '[data-short-like]', data.liked);
                setActionState(item, '[data-short-dislike]', false);
                setText(item, '[data-short-likes-count]', data.likes_count);
                setText(item, '[data-short-dislikes-count]', data.dislikes_count);
            } catch {
                showMessage('Beğeni işlemi tamamlanamadı.');
            } finally {
                button.disabled = false;
            }
        }));

        item.querySelectorAll('[data-short-dislike]').forEach((button) => button.addEventListener('click', async () => {
            if (!requireAuth() || button.disabled) return;
            button.disabled = true;
            try {
                const data = await post(button.dataset.url);
                setActionState(item, '[data-short-dislike]', data.disliked);
                setActionState(item, '[data-short-like]', false);
                setText(item, '[data-short-likes-count]', data.likes_count);
                setText(item, '[data-short-dislikes-count]', data.dislikes_count);
            } catch {
                showMessage('Beğenmeme işlemi tamamlanamadı.');
            } finally {
                button.disabled = false;
            }
        }));

        item.querySelectorAll('[data-short-save]').forEach((button) => button.addEventListener('click', async () => {
            if (!requireAuth() || button.disabled) return;
            button.disabled = true;
            try {
                const data = await post(button.dataset.url);
                setActionState(item, '[data-short-save]', data.saved);
                setText(item, '[data-short-save-text]', data.saved ? 'Kayıtlı' : 'Kaydet');
                showMessage(data.message);
            } catch {
                showMessage('Kaydetme işlemi tamamlanamadı.');
            } finally {
                button.disabled = false;
            }
        }));

        item.querySelector('[data-short-subscribe]')?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            if (button.disabled) return;
            button.disabled = true;
            try {
                const data = await post(button.dataset.url);
                button.dataset.active = String(data.subscribed);
                button.querySelector('[data-short-subscribe-text]').textContent = data.subscribed ? 'Abone olundu' : 'Abone ol';
                button.classList.toggle('bg-zinc-800', data.subscribed);
                button.classList.toggle('text-zinc-100', data.subscribed);
                button.classList.toggle('bg-red-600', !data.subscribed);
                button.classList.toggle('text-white', !data.subscribed);
            } catch {
                showMessage('Abonelik işlemi tamamlanamadı.');
            } finally {
                button.disabled = false;
            }
        });

        item.querySelectorAll('[data-short-share]').forEach((button) => button.addEventListener('click', async () => {
            const shareData = { title: document.title, url: window.location.href };
            try {
                if (navigator.share) {
                    await navigator.share(shareData);
                } else if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(window.location.href);
                    showMessage('Shorts bağlantısı kopyalandı.');
                } else {
                    window.prompt('Bağlantıyı kopyala:', window.location.href);
                }
            } catch (error) {
                if (error?.name !== 'AbortError') showMessage('Paylaşım başlatılamadı.');
            }
        }));

        const commentsSheet = item.querySelector('[data-short-comments-sheet]');
        let commentsHistoryActive = false;

        const closeComments = ({ fromHistory = false } = {}) => {
            if (!commentsSheet || commentsSheet.hidden) return;

            const shouldRestoreHistory = commentsHistoryActive && !fromHistory;
            commentsHistoryActive = false;
            commentsSheet.classList.remove('is-open');
            item.classList.remove('is-comments-open');
            document.body.classList.remove('overflow-hidden');
            shortsFeed.classList.remove('overflow-y-hidden');
            window.setTimeout(() => { commentsSheet.hidden = true; }, 240);

            if (shouldRestoreHistory) {
                window.history.back();
            }
        };

        const openComments = () => {
            if (!commentsSheet || !commentsSheet.hidden) return;

            commentsSheet.hidden = false;
            item.classList.add('is-comments-open');
            document.body.classList.add('overflow-hidden');
            shortsFeed.classList.add('overflow-y-hidden');
            window.requestAnimationFrame(() => commentsSheet.classList.add('is-open'));

            if (window.innerWidth < 1024 && !window.history.state?.turtubeShortComments) {
                window.history.pushState({
                    ...(window.history.state || {}),
                    turtubeShortComments: item.dataset.shortId,
                }, '');
                commentsHistoryActive = true;
            }
        };

        item.querySelectorAll('[data-short-comments-open]').forEach((button) => button.addEventListener('click', openComments));
        item.querySelectorAll('[data-short-sheet-close]').forEach((button) => button.addEventListener('click', () => closeComments()));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !commentsSheet?.hidden) {
                closeComments();
            }
        });
        window.addEventListener('popstate', () => {
            if (!commentsSheet?.hidden) {
                closeComments({ fromHistory: true });
            }
        });

        item.querySelector('[data-short-comment-form]')?.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!requireAuth()) return;
            const form = event.currentTarget;
            const submit = form.querySelector('button[type="submit"]');
            if (submit.disabled) return;
            submit.disabled = true;
            try {
                const data = await post(form.action, new FormData(form));
                appendComment(item, data.comment);
                setText(item, '[data-short-comments-count]', data.comments_count);
                form.reset();
            } catch {
                showMessage('Yorum gönderilemedi.');
            } finally {
                submit.disabled = false;
            }
        });

        item.querySelectorAll('[data-comment-reaction]').forEach((button) => bindCommentReaction(item, button));

        const reportDialog = item.querySelector('[data-short-report-dialog]');
        item.querySelectorAll('[data-short-report-open]').forEach((button) => button.addEventListener('click', () => {
            if (!isAuthenticated) {
                window.location.assign(loginUrl);
                return;
            }
            reportDialog.hidden = false;
            video.pause();
        }));
        item.querySelectorAll('[data-short-report-close]').forEach((button) => button.addEventListener('click', () => {
            reportDialog.hidden = true;
        }));
        item.querySelector('[data-short-report-form]')?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = event.currentTarget;
            const submit = form.querySelector('button[type="submit"]');
            if (submit.disabled) return;
            submit.disabled = true;
            try {
                const data = await post(form.action, new FormData(form));
                reportDialog.hidden = true;
                form.reset();
                showMessage(data.message || 'Raporunuz alındı.');
            } catch {
                showMessage('Rapor gönderilemedi.');
            } finally {
                submit.disabled = false;
            }
        });
    });

    shortsFeed.addEventListener('wheel', (event) => {
        if (wheelLocked || Math.abs(event.deltaY) < 24) return;
        event.preventDefault();
        wheelLocked = true;
        moveTo(activeIndex() + (event.deltaY > 0 ? 1 : -1));
        window.setTimeout(() => { wheelLocked = false; }, 550);
    }, { passive: false });
    shortsFeed.addEventListener('touchstart', (event) => { touchStartY = event.changedTouches[0]?.clientY || 0; }, { passive: true });
    shortsFeed.addEventListener('touchend', (event) => {
        const distance = touchStartY - (event.changedTouches[0]?.clientY || 0);
        if (Math.abs(distance) > 55) moveTo(activeIndex() + (distance > 0 ? 1 : -1));
    }, { passive: true });
    shortsFeed.addEventListener('scroll', () => window.requestAnimationFrame(syncPlayback), { passive: true });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                const player = entry.target.querySelector('[data-short-video]');
                if (!player) return;
                if (entry.isIntersecting && entry.intersectionRatio > 0.72) {
                    items.forEach((other) => {
                        if (other !== entry.target) other.querySelector('[data-short-video]')?.pause();
                    });
                    player.play().catch(() => {});
                } else if (!entry.isIntersecting) {
                    player.pause();
                }
            });
        }, { root: shortsFeed, threshold: [0, 0.72] });
        items.forEach((item) => observer.observe(item));
    }

    // The first Short starts muted. Browser autoplay policies can still block it safely.
    items[0]?.querySelector('[data-short-video]')?.play().catch(() => {});
}
