@props(['video', 'isFavorited' => false])

@auth
    <button id="favoriteButton" type="button" data-url="{{ route('videos.favorite', $video) }}" class="rounded-full px-5 py-3 font-medium transition {{ $isFavorited ? 'bg-amber-500 text-zinc-950 hover:bg-amber-400' : 'border border-gray-700 bg-gray-800 text-white hover:bg-gray-700' }}">
        <span id="favoriteText">{{ $isFavorited ? '★ Favorilerde' : '☆ Favoriye ekle' }}</span>
    </button>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const button = document.getElementById('favoriteButton');
        const text = document.getElementById('favoriteText');
        if (!button || !text) return;
        button.addEventListener('click', async () => {
            try {
                const response = await fetch(button.dataset.url, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error();
                const data = await response.json();
                text.textContent = data.favorited ? '★ Favorilerde' : '☆ Favoriye ekle';
                button.className = `rounded-full px-5 py-3 font-medium transition ${data.favorited ? 'bg-amber-500 text-zinc-950 hover:bg-amber-400' : 'border border-gray-700 bg-gray-800 text-white hover:bg-gray-700'}`;
            } catch { alert('Favori işlemi gerçekleştirilemedi. Lütfen tekrar deneyin.'); }
        });
    });
    </script>
@else
    <a href="{{ route('login') }}" class="rounded-full border border-gray-700 bg-gray-800 px-5 py-3 font-medium text-white transition hover:bg-gray-700">☆ Favoriye ekle</a>
@endauth
