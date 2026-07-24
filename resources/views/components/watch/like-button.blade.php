@auth

<button
    id="likeButton"
    type="button"
    data-url="{{ route('videos.like', $video) }}"
    class="rounded-full px-5 py-3 transition {{ $isLiked
        ? 'bg-red-600 hover:bg-red-700 text-white'
        : 'bg-gray-800 hover:bg-gray-700 text-white border border-gray-700' }}">

    ❤️ <span id="likesCount">{{ number_format($video->likes_count) }}</span>

</button>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const button = document.getElementById('likeButton');

    if (!button) return;

    const count = document.getElementById('likesCount');

    button.addEventListener('click', async () => {

        try {

            const response = await fetch(button.dataset.url, {

                method: 'POST',

                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }

            });

            if (!response.ok) {
                throw new Error('İstek başarısız.');
            }

            const data = await response.json();

            count.textContent = data.likes_count;

            if (data.liked) {

                button.classList.remove(
                    'bg-gray-800',
                    'hover:bg-gray-700',
                    'border',
                    'border-gray-700'
                );

                button.classList.add(
                    'bg-red-600',
                    'hover:bg-red-700',
                    'text-white'
                );

            } else {

                button.classList.remove(
                    'bg-red-600',
                    'hover:bg-red-700'
                );

                button.classList.add(
                    'bg-gray-800',
                    'hover:bg-gray-700',
                    'border',
                    'border-gray-700',
                    'text-white'
                );

            }

        } catch (error) {

            console.error(error);

            alert('Beğeni işlemi sırasında bir hata oluştu.');

        }

    });

});
</script>

@else

<a
    href="{{ route('login') }}"
    class="inline-flex items-center justify-center rounded-full border border-gray-700 bg-gray-800 px-5 py-3 text-white transition hover:bg-gray-700">

    ❤️ {{ number_format($video->likes_count) }}

</a>

@endauth