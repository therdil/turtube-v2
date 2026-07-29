@auth

<div class="relative" data-playlist-menu>
    <button
        type="button"
        data-playlist-trigger
        class="rounded-full border border-gray-700 bg-gray-800 px-5 py-3 text-white transition hover:bg-gray-700">
        + Listeye ekle
    </button>

    <div
        data-playlist-panel
        class="absolute right-0 z-20 mt-2 hidden w-72 rounded-xl border border-gray-700 bg-gray-900 p-3 shadow-xl">
        <p class="px-2 pb-2 text-sm font-semibold text-white">Oynatma listelerine kaydet</p>

        @forelse ($playlists as $playlist)
            <button
                type="button"
                data-playlist-item
                data-url="{{ route('playlists.toggle', $playlist) }}"
                data-added="{{ $playlistVideoIds->contains($playlist->id) ? 'true' : 'false' }}"
                class="flex w-full items-center justify-between rounded-lg px-2 py-2 text-left text-sm text-gray-200 transition hover:bg-gray-800">
                <span class="truncate">{{ $playlist->name }}</span>
                <span data-playlist-status class="ml-3 text-red-400">
                    {{ $playlistVideoIds->contains($playlist->id) ? 'Eklendi' : 'Ekle' }}
                </span>
            </button>
        @empty
            <p class="px-2 py-2 text-sm text-gray-400">Henüz bir listen yok.</p>
        @endforelse

        <a href="{{ route('playlists.create') }}" class="mt-2 block rounded-lg bg-red-600 px-3 py-2 text-center text-sm font-medium text-white hover:bg-red-700">
            + Yeni playlist oluştur
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-playlist-menu]').forEach((menu) => {
        const trigger = menu.querySelector('[data-playlist-trigger]');
        const panel = menu.querySelector('[data-playlist-panel]');

        trigger.addEventListener('click', () => panel.classList.toggle('hidden'));

        menu.querySelectorAll('[data-playlist-item]').forEach((item) => {
            item.addEventListener('click', async () => {
                item.disabled = true;

                try {
                    const response = await fetch(item.dataset.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ video_id: {{ $video->id }} }),
                    });

                    if (!response.ok) throw new Error('İstek başarısız.');

                    const data = await response.json();
                    item.dataset.added = data.added ? 'true' : 'false';
                    item.querySelector('[data-playlist-status]').textContent = data.added ? 'Eklendi' : 'Ekle';
                } catch (error) {
                    console.error(error);
                    alert('İşlem sırasında bir hata oluştu.');
                } finally {
                    item.disabled = false;
                }
            });
        });
    });
});
</script>

@else

<a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-gray-700 bg-gray-800 px-5 py-3 text-white transition hover:bg-gray-700">
    + Listeye ekle
</a>

@endauth
