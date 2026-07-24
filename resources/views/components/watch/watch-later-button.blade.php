@auth

<button
    id="watchLaterButton"
    type="button"
    data-url="{{ route('watch-later.toggle', $video) }}"
    class="rounded-full px-5 py-3 transition
    {{ $isWatchLater
        ? 'bg-blue-600 hover:bg-blue-700 text-white'
        : 'bg-gray-800 hover:bg-gray-700 text-white border border-gray-700' }}">

    <span id="watchLaterText">
        {{ $isWatchLater ? '✔ Daha Sonra İzlendi' : '💾 Daha Sonra İzle' }}
    </span>

</button>

<script>

document.addEventListener('DOMContentLoaded', () => {

    const button = document.getElementById('watchLaterButton');

    if (!button) return;

    const text = document.getElementById('watchLaterText');

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

            if (data.saved) {

                text.textContent = '✔ Daha Sonra İzlendi';

                button.classList.remove(
                    'bg-gray-800',
                    'hover:bg-gray-700',
                    'border',
                    'border-gray-700'
                );

                button.classList.add(
                    'bg-blue-600',
                    'hover:bg-blue-700',
                    'text-white'
                );

            } else {

                text.textContent = '💾 Daha Sonra İzle';

                button.classList.remove(
                    'bg-blue-600',
                    'hover:bg-blue-700'
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

            alert('İşlem sırasında bir hata oluştu.');

        }

    });

});

</script>

@else

<a
    href="{{ route('login') }}"
    class="inline-flex items-center justify-center rounded-full border border-gray-700 bg-gray-800 px-5 py-3 text-white transition hover:bg-gray-700">

    💾 Daha Sonra İzle

</a>

@endauth