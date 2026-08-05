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
            // Ağ hatası durumunda kullanıcı mevcut Shorts kartlarını izlemeye devam eder.
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
    let wheelLocked = false;
    let touchStartY = 0;

    const activeIndex = () => {
        const feedBounds = shortsFeed.getBoundingClientRect();
        return items.reduce((closestIndex, item, index) => {
            const currentDistance = Math.abs(item.getBoundingClientRect().top - feedBounds.top);
            const closestDistance = Math.abs(items[closestIndex].getBoundingClientRect().top - feedBounds.top);
            return currentDistance < closestDistance ? index : closestIndex;
        }, 0);
    };

    const moveTo = (index) => items[Math.max(0, Math.min(index, items.length - 1))]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    const syncPlayback = () => {
        const current = activeIndex();
        items.forEach((item, index) => {
            const player = item.querySelector('video');
            if (!player) return;
            if (index === current) player.play().catch(() => {}); else player.pause();
        });
    };

    shortsFeed.addEventListener('scroll', () => window.requestAnimationFrame(syncPlayback), { passive: true });
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
    items.forEach((item, index) => item.querySelector('video')?.addEventListener('ended', () => moveTo(index + 1)));
    syncPlayback();
}
