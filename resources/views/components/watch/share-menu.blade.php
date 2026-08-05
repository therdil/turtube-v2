@props(['video'])

@php($shareUrl = route('videos.show', $video))
<details class="relative">
    <summary class="cursor-pointer list-none rounded-full border border-gray-700 bg-gray-800 px-5 py-3 font-medium text-white transition hover:bg-gray-700">Paylaş</summary>
    <div class="absolute left-0 z-30 mt-3 flex w-64 flex-col gap-2 rounded-2xl border border-gray-700 bg-gray-950 p-3 shadow-2xl">
        <a target="_blank" rel="noopener noreferrer" href="https://wa.me/?text={{ rawurlencode($video->title.' '.$shareUrl) }}" class="rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500">WhatsApp</a>
        <a target="_blank" rel="noopener noreferrer" href="https://twitter.com/intent/tweet?text={{ rawurlencode($video->title) }}&url={{ rawurlencode($shareUrl) }}" class="rounded-xl bg-sky-600 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-500">X'te paylaş</a>
        <a target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}" class="rounded-xl bg-blue-700 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-600">Facebook</a>
        <button type="button" data-copy-share-url="{{ $shareUrl }}" class="rounded-xl border border-gray-700 px-3 py-2 text-sm font-semibold text-gray-200 hover:border-red-500 hover:text-white">Bağlantıyı kopyala</button>
    </div>
</details>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-copy-share-url]').forEach((button) => button.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(button.dataset.copyShareUrl); button.textContent = 'Bağlantı kopyalandı'; }
        catch { window.prompt('Bağlantıyı kopyalayın:', button.dataset.copyShareUrl); }
    }));
});
</script>
