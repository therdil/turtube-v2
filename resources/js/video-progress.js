const player = document.getElementById('video-player');

if (player) {

    let lastSaved = 0;

    player.addEventListener('timeupdate', () => {

        if (player.currentTime - lastSaved < 5) {
            return;
            const startSeconds = Number(player.dataset.startSeconds || 0);

    player.addEventListener('loadedmetadata', () => {

        if (startSeconds > 5 && startSeconds < player.duration - 5) {
        player.currentTime = startSeconds;
    }

    });
        }

        lastSaved = player.currentTime;

        const duration = player.duration || 0;

        if (!duration) {
            return;
        }

        fetch(player.dataset.progressUrl, {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': player.dataset.csrf,
                'Accept': 'application/json',
            },

            body: JSON.stringify({

                seconds: Math.floor(player.currentTime),

                percentage: Number(
                    (player.currentTime / duration * 100).toFixed(2)
                )

                

            })

        });

    });

}