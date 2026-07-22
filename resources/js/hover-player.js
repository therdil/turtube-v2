let currentVideo = null;
let hoverTimer = null;

function updateProgress(card) {
    const preview = card.querySelector(".preview-video");
    const progress = card.querySelector(".preview-progress");

    if (!preview || !progress) return;

    if (!preview.duration) {
        progress.style.width = "0%";
        return;
    }

    const percent = (preview.currentTime / preview.duration) * 100;
    progress.style.width = `${percent}%`;
}

function resetMute(card) {
    const preview = card.querySelector(".preview-video");
    const button = card.querySelector(".mute-button");

    if (!preview || !button) return;

    preview.muted = true;
    button.textContent = "🔇";
}

function stopPreview(card) {

    const thumbnail = card.querySelector(".thumbnail");
    const preview = card.querySelector(".preview-video");
    const progress = card.querySelector(".preview-progress");
    const button = card.querySelector(".mute-button");

    if (!preview) return;

    preview.pause();
    preview.currentTime = 0;

    preview.style.opacity = "0";
    preview.style.pointerEvents = "none";

    if (thumbnail) {
        thumbnail.style.opacity = "1";
    }

    if (progress) {
        progress.style.width = "0%";
    }

    if (button) {
        button.classList.add("hidden");
        button.classList.remove("flex");
    }

    resetMute(card);

    if (currentVideo === preview) {
        currentVideo = null;
    }
}

function playPreview(card) {

    const thumbnail = card.querySelector(".thumbnail");
    const preview = card.querySelector(".preview-video");
    const button = card.querySelector(".mute-button");

    if (!preview) return;

    if (currentVideo && currentVideo !== preview) {

        const oldCard = currentVideo.closest(".video-card");

        if (oldCard) {
            stopPreview(oldCard);
        }

    }

    currentVideo = preview;

    preview.muted = true;
    preview.currentTime = 0;

    if (thumbnail) {
        thumbnail.style.opacity = "0";
    }

    preview.style.opacity = "1";
    preview.style.pointerEvents = "auto";

    if (button) {
        button.classList.remove("hidden");
        button.classList.add("flex");
        button.textContent = "🔇";
    }

    preview.play().catch(() => {});
}

document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".video-card").forEach(card => {

        const preview = card.querySelector(".preview-video");
        const button = card.querySelector(".mute-button");

        if (!preview) return;

        card.addEventListener("mouseenter", () => {

            clearTimeout(hoverTimer);

            hoverTimer = setTimeout(() => {
                playPreview(card);
            }, 300);

        });

        card.addEventListener("mouseleave", () => {

            clearTimeout(hoverTimer);

            stopPreview(card);

        });

        preview.addEventListener("timeupdate", () => {
            updateProgress(card);
        });

        preview.addEventListener("ended", () => {
            stopPreview(card);
        });

        if (button) {

            button.addEventListener("click", (event) => {

                event.preventDefault();
                event.stopPropagation();

                preview.muted = !preview.muted;

                button.textContent = preview.muted ? "🔇" : "🔊";

            });

        }

    });

});