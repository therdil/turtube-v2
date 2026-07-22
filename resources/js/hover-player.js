let currentVideo = null;

function stopPreview(card) {

    const thumbnail = card.querySelector(".thumbnail");
    const preview = card.querySelector(".preview-video");

    if (!preview) {
        return;
    }

    preview.pause();
    preview.currentTime = 0;

    preview.classList.add("hidden");

    if (thumbnail) {
        thumbnail.style.opacity = "1";
    }

    if (currentVideo === preview) {
        currentVideo = null;
    }
}

function playPreview(card) {

    const thumbnail = card.querySelector(".thumbnail");
    const preview = card.querySelector(".preview-video");

    if (!preview) {
        return;
    }

    // Başka bir video oynuyorsa durdur
    if (currentVideo && currentVideo !== preview) {

        const oldCard = currentVideo.closest(".video-card");

        if (oldCard) {
            stopPreview(oldCard);
        }

    }

    currentVideo = preview;

    if (thumbnail) {
        thumbnail.style.opacity = "0";
    }

    preview.classList.remove("hidden");
    preview.currentTime = 0;

    preview.play().catch(() => {});
}

document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".video-card").forEach(card => {

        const preview = card.querySelector(".preview-video");

        if (!preview) {
            return;
        }

        card.addEventListener("mouseenter", () => {
            playPreview(card);
        });

        card.addEventListener("mouseleave", () => {
            stopPreview(card);
        });

        preview.addEventListener("ended", () => {
            stopPreview(card);
        });

    });

});