<div class="overflow-hidden rounded-2xl bg-black shadow-2xl">

    <video
        controls
        autoplay
        class="aspect-video w-full bg-black"
    >

        <source
            src="{{ asset('storage/' . $video->video_path) }}"
            type="video/mp4">

        Tarayıcınız video oynatmayı desteklemiyor.

    </video>

</div>