let currentVideo = null;

function updateProgress(card) {
    const preview = card.querySelector(".preview-video");
    const progress = card.querySelector(".preview-progress");

    if (!preview || !progress) {
        return;
    }

    if (!preview.duration) {
        progress.style.width = "0%";
        return;
    }

    const percent = (preview.currentTime / preview.duration) * 100;
    progress.style.width = `${percent}%`;
}

function stopPreview(card) {
    const thumbnail = card.querySelector(".thumbnail");
    const preview = card.querySelector(".preview-video");
    const progress = card.querySelector(".preview-progress");

    if (!preview) {
        return;
    }

    preview.pause();
    preview.currentTime = 0;

    preview.classList.add("hidden");

    if (thumbnail) {
        thumbnail.style.opacity = "1";
    }

    if (progress) {
        progress.style.width = "0%";
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

        preview.addEventListener("timeupdate", () => {
            updateProgress(card);
        });

        preview.addEventListener("ended", () => {
            stopPreview(card);
        });

    });

});