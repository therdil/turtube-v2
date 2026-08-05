document.querySelectorAll('[data-live-search-form]').forEach((form) => {
    const input = form.querySelector('[data-live-search-input]');
    const results = form.querySelector('[data-live-search-results]');
    const endpoint = input?.dataset.suggestionsUrl;
    let timer;
    let controller;

    const close = () => {
        results?.classList.add('hidden');
        input?.setAttribute('aria-expanded', 'false');
    };

    const addLink = (parent, href, text, className) => {
        const link = document.createElement('a');
        link.href = href;
        link.className = className;
        link.textContent = text;
        parent.append(link);
    };

    const render = (payload) => {
        if (!results || !input) return;
        results.replaceChildren();
        const content = document.createElement('div');
        content.className = 'divide-y divide-zinc-800';

        if (payload.queries?.length) {
            const group = document.createElement('div');
            group.className = 'p-2';
            const title = document.createElement('p');
            title.className = 'px-3 py-2 text-xs font-semibold uppercase tracking-wider text-zinc-500';
            title.textContent = 'Arama önerileri';
            group.append(title);
            payload.queries.forEach((query) => addLink(group, `/search?q=${encodeURIComponent(query)}`, query, 'block rounded-xl px-3 py-2 text-sm text-zinc-300 transition hover:bg-zinc-900 hover:text-white'));
            content.append(group);
        }

        if (payload.videos?.length) {
            const group = document.createElement('div');
            group.className = 'p-2';
            const title = document.createElement('p');
            title.className = 'px-3 py-2 text-xs font-semibold uppercase tracking-wider text-zinc-500';
            title.textContent = 'Videolar';
            group.append(title);
            payload.videos.forEach((video) => {
                const link = document.createElement('a');
                link.href = video.url;
                link.className = 'flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-zinc-900';
                if (video.thumbnail) {
                    const image = document.createElement('img');
                    image.src = video.thumbnail;
                    image.alt = '';
                    image.className = 'h-10 w-16 rounded-lg object-cover';
                    link.append(image);
                }
                const details = document.createElement('span');
                details.className = 'min-w-0 flex-1';
                const name = document.createElement('span');
                name.className = 'block truncate text-sm font-medium text-white';
                name.textContent = video.title;
                const views = document.createElement('span');
                views.className = 'mt-0.5 block text-xs text-zinc-500';
                views.textContent = `${video.views} görüntülenme`;
                details.append(name, views);
                link.append(details);
                group.append(link);
            });
            content.append(group);
        }

        if (!content.childElementCount) return close();
        results.append(content);
        results.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
    };

    input?.addEventListener('input', () => {
        const query = input.value.trim();
        window.clearTimeout(timer);
        controller?.abort();
        if (query.length < 2 || !endpoint) return close();

        timer = window.setTimeout(async () => {
            controller = new AbortController();
            try {
                const response = await fetch(`${endpoint}?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                if (response.ok) render(await response.json());
            } catch (error) {
                if (error.name !== 'AbortError') close();
            }
        }, 220);
    });

    input?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
    document.addEventListener('click', (event) => {
        if (!form.contains(event.target)) close();
    });
});
