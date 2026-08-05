@props(['video', 'userRating' => null, 'ratingAverage' => 0, 'ratingsCount' => 0])

<div class="flex items-center gap-2 rounded-full border border-gray-700 bg-gray-800 px-4 py-2.5" aria-label="Video puanlama">
    <div class="flex items-center gap-0.5" data-rating-control data-url="{{ route('videos.rating', $video) }}">
        @for ($star = 1; $star <= 5; $star++)
            @auth
                <button type="button" data-rating-value="{{ $star }}" class="text-lg leading-none transition {{ $userRating && $star <= $userRating ? 'text-amber-400' : 'text-gray-600 hover:text-amber-300' }}" aria-label="{{ $star }} yıldız">★</button>
            @else
                <a href="{{ route('login') }}" class="text-lg leading-none {{ $ratingAverage >= $star ? 'text-amber-400' : 'text-gray-600' }}" aria-label="Puanlamak için giriş yap">★</a>
            @endauth
        @endfor
    </div>
    <span data-rating-summary class="whitespace-nowrap text-xs text-gray-400">{{ number_format($ratingAverage, 1) }} ({{ number_format($ratingsCount) }})</span>
</div>

@auth
<script>
document.addEventListener('DOMContentLoaded', () => {
    const control = document.querySelector('[data-rating-control]');
    if (!control) return;
    const summary = document.querySelector('[data-rating-summary]');
    const buttons = Array.from(control.querySelectorAll('[data-rating-value]'));
    const paint = (rating) => buttons.forEach((button) => { const active = Number(button.dataset.ratingValue) <= rating; button.classList.toggle('text-amber-400', active); button.classList.toggle('text-gray-600', !active); });
    buttons.forEach((button) => button.addEventListener('click', async () => {
        const rating = Number(button.dataset.ratingValue);
        try {
            const response = await fetch(control.dataset.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ rating }) });
            if (!response.ok) throw new Error();
            const data = await response.json();
            paint(data.rating);
            if (summary) summary.textContent = `${Number(data.average).toFixed(1)} (${data.count})`;
        } catch { alert('Puan kaydedilemedi. Lütfen tekrar deneyin.'); }
    }));
});
</script>
@endauth
