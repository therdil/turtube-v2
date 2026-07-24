@auth

@if(auth()->id() !== $video->user_id)

<button
    id="subscribeButton"
    type="button"
    data-url="{{ route('channels.subscribe', $video->user) }}"
    class="rounded-full px-6 py-3 font-semibold transition {{ $isSubscribed
        ? 'bg-gray-200 text-black hover:bg-gray-300'
        : 'bg-red-600 text-white hover:bg-red-700' }}">

    <span id="subscribeText">
        {{ $isSubscribed ? '✓ Abone Olundu' : 'Abone Ol' }}
    </span>

    •
    <span id="subscriberCount">
        {{ number_format($subscribersCount) }}
    </span>

</button>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const button = document.getElementById('subscribeButton');

    if (!button) return;

    const text = document.getElementById('subscribeText');
    const count = document.getElementById('subscriberCount');

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

            count.textContent = data.subscribers_count;

            if (data.subscribed) {

                text.textContent = '✓ Abone Olundu';

                button.classList.remove(
                    'bg-red-600',
                    'hover:bg-red-700',
                    'text-white'
                );

                button.classList.add(
                    'bg-gray-200',
                    'hover:bg-gray-300',
                    'text-black'
                );

            } else {

                text.textContent = 'Abone Ol';

                button.classList.remove(
                    'bg-gray-200',
                    'hover:bg-gray-300',
                    'text-black'
                );

                button.classList.add(
                    'bg-red-600',
                    'hover:bg-red-700',
                    'text-white'
                );

            }

        } catch (error) {

            console.error(error);

            alert('Abonelik işlemi sırasında bir hata oluştu.');

        }

    });

});
</script>

@endif

@else

<a
    href="{{ route('login') }}"
    class="rounded-full bg-red-600 px-6 py-3 text-white hover:bg-red-700 transition">

    Abone Ol

</a>

@endauth