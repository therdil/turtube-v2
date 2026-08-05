const player = document.getElementById('video-player');

if (player) {
    const shell = document.getElementById('watch-player-shell');
    const playerFrame = shell?.querySelector('[data-player-frame]');
    const loadingIndicator = shell?.querySelector('[data-player-loading]');
    const bufferIndicator = shell?.querySelector('[data-player-buffer]');
    const volumeIndicator = shell?.querySelector('[data-player-volume-indicator]');
    const volumeLabel = shell?.querySelector('[data-player-volume-label]');
    const volumeLevel = shell?.querySelector('[data-player-volume-level]');
    const qualityTrigger = document.querySelector('[data-player-quality-trigger]');
    const qualityLabel = document.querySelector('[data-player-quality-label]');
    const qualityOptions = document.querySelectorAll('[data-player-quality-option]');
    const speedTrigger = document.querySelector('[data-player-speed-trigger]');
    const speedLabel = document.querySelector('[data-player-speed-label]');
    const speedOptions = document.querySelectorAll('[data-player-speed-option]');
    const dropdownTriggers = document.querySelectorAll('[data-player-dropdown-trigger]');
    const dropdownMenus = document.querySelectorAll('[data-player-dropdown-menu]');
    const captionsButton = document.querySelector('[data-player-captions]');
    const chapterSelect = document.querySelector('[data-player-chapter]');
    const previousChapterButton = document.querySelector('[data-player-previous-chapter]');
    const nextChapterButton = document.querySelector('[data-player-next-chapter]');
    const miniButton = document.querySelector('[data-player-mini]');
    const miniCloseButton = document.querySelector('[data-player-mini-close]');
    const pipButton = document.querySelector('[data-player-pip]');
    const cinemaButton = document.querySelector('[data-player-cinema]');
    const cinemaLabel = document.querySelector('[data-player-cinema-label]');
    const autoplayToggle = document.querySelector('[data-player-autoplay]');
    const endScreen = document.getElementById('video-end-screen');
    const replayButton = document.querySelector('[data-player-replay]');
    const countdown = document.getElementById('autoplay-countdown');
    const status = document.querySelector('[data-player-status]');
    const nextUrl = shell?.dataset.nextUrl;

    const parseData = (value, fallback) => {
        try {
            return JSON.parse(value || '');
        } catch {
            return fallback;
        }
    };

    const sources = parseData(player.dataset.sources, []);
    const chapters = parseData(player.dataset.chapters, []);
    let lastSaved = 0;
    let restoredProgress = false;
    let autoplayTimer;
    let volumeIndicatorTimer;

    const announce = (message) => {
        if (status) status.textContent = message;
    };

    const setLoading = (visible) => {
        loadingIndicator?.classList.toggle('hidden', !visible);
        loadingIndicator?.classList.toggle('flex', visible);
        playerFrame?.setAttribute('aria-busy', String(visible));
    };

    const syncBuffer = () => {
        if (!bufferIndicator || !player.duration || !Number.isFinite(player.duration)) return;

        const ranges = player.buffered;
        const bufferedEnd = ranges.length ? ranges.end(ranges.length - 1) : 0;
        bufferIndicator.style.width = `${Math.min(100, (bufferedEnd / player.duration) * 100)}%`;
    };

    const showVolumeFeedback = () => {
        if (!volumeIndicator || !volumeLabel || !volumeLevel) return;

        const volume = player.muted ? 0 : Math.round(player.volume * 100);
        volumeLabel.textContent = volume === 0 ? 'Ses kapalı' : `Ses %${volume}`;
        volumeLevel.style.width = `${volume}%`;
        volumeIndicator.classList.remove('hidden');
        volumeIndicator.classList.add('flex');
        window.clearTimeout(volumeIndicatorTimer);
        volumeIndicatorTimer = window.setTimeout(() => {
            volumeIndicator.classList.add('hidden');
            volumeIndicator.classList.remove('flex');
        }, 1200);
    };

    const saveProgress = () => {
        if (!player.dataset.progressUrl) return;

        const duration = player.duration || 0;

        if (!duration || !Number.isFinite(player.currentTime)) return;

        fetch(player.dataset.progressUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': player.dataset.csrf,
                Accept: 'application/json',
            },
            body: JSON.stringify({
                seconds: Math.floor(player.currentTime),
                percentage: Number(((player.currentTime / duration) * 100).toFixed(2)),
            }),
            keepalive: true,
        }).catch(() => {
            // İlerleme kaydı başarısız olursa oynatma kesilmemelidir.
        });
    };

    const clearAutoplayTimer = () => {
        if (autoplayTimer) window.clearInterval(autoplayTimer);
        autoplayTimer = undefined;
    };

    const captionsAreVisible = () => Array.from(player.textTracks).some(
        (track) => track.mode === 'showing',
    );

    const syncCaptionButton = () => {
        if (!captionsButton) return;
        const visible = captionsAreVisible();
        captionsButton.setAttribute('aria-pressed', String(visible));
        captionsButton.dataset.active = String(visible);
    };

    const toggleCaptions = () => {
        const makeVisible = !captionsAreVisible();
        Array.from(player.textTracks).forEach((track, index) => {
            track.mode = makeVisible && index === 0 ? 'showing' : 'disabled';
        });
        syncCaptionButton();
        announce(makeVisible ? 'Altyazı açıldı.' : 'Altyazı kapatıldı.');
    };

    const currentChapterIndex = () => {
        let index = 0;
        chapters.forEach((chapter, chapterIndex) => {
            if (player.currentTime >= chapter.start) index = chapterIndex;
        });
        return index;
    };

    const seekToChapter = (index) => {
        if (!chapters.length) return;
        const targetIndex = Math.max(0, Math.min(index, chapters.length - 1));
        const chapter = chapters[targetIndex];
        player.currentTime = Number(chapter.start);
        player.play().catch(() => {});
        if (chapterSelect) chapterSelect.value = String(chapter.start);
        announce(`${chapter.title} bölümüne geçildi.`);
    };

    const syncCurrentChapter = () => {
        if (!chapterSelect || !chapters.length) return;
        chapterSelect.value = String(chapters[currentChapterIndex()].start);
    };

    const changeQuality = (url) => {
        if (!url || url === player.currentSrc || url === player.src) return;

        const currentTime = player.currentTime;
        const wasPlaying = !player.paused;
        const currentRate = player.playbackRate;

        playerFrame?.classList.add('scale-[0.99]', 'opacity-80');
        player.src = url;
        player.load();
        player.addEventListener('loadedmetadata', function restoreQualityState() {
            player.removeEventListener('loadedmetadata', restoreQualityState);
            player.currentTime = Math.min(currentTime, Number.isFinite(player.duration) ? player.duration : currentTime);
            player.playbackRate = currentRate;
            if (wasPlaying) player.play().catch(() => {});
            playerFrame?.classList.remove('scale-[0.99]', 'opacity-80');
        });

        const source = sources.find((item) => item.url === url);
        announce(`Kalite ${source?.label || ''} olarak değiştirildi.`);
    };

    player.addEventListener('loadedmetadata', () => {
        if (!restoredProgress) {
            const startSeconds = Number(player.dataset.startSeconds || 0);
            if (startSeconds > 5 && startSeconds < player.duration - 5) {
                player.currentTime = startSeconds;
            }
            restoredProgress = true;
        }
        syncCaptionButton();
        syncCurrentChapter();
        syncBuffer();
    });

    player.addEventListener('loadstart', () => setLoading(true));
    player.addEventListener('waiting', () => setLoading(true));
    player.addEventListener('seeking', () => setLoading(true));
    player.addEventListener('canplay', () => setLoading(false));
    player.addEventListener('playing', () => setLoading(false));
    player.addEventListener('seeked', () => setLoading(false));
    player.addEventListener('progress', syncBuffer);
    player.addEventListener('volumechange', showVolumeFeedback);
    player.addEventListener('error', () => {
        setLoading(false);
        playerFrame?.classList.remove('scale-[0.99]', 'opacity-80');
    });

    player.addEventListener('timeupdate', () => {
        if (player.currentTime - lastSaved >= 5) {
            lastSaved = player.currentTime;
            saveProgress();
        }
        syncCurrentChapter();
    });

    player.addEventListener('pause', saveProgress);
    window.addEventListener('pagehide', saveProgress);

    const closeDropdowns = () => {
        dropdownMenus.forEach((menu) => menu.classList.add('hidden'));
        dropdownTriggers.forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));
    };

    dropdownTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const menu = trigger.nextElementSibling;
            const willOpen = menu?.classList.contains('hidden');
            closeDropdowns();
            if (willOpen) {
                menu?.classList.remove('hidden');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;
        if (!event.target.closest('[data-player-dropdown-trigger], [data-player-dropdown-menu]')) {
            closeDropdowns();
        }
    });

    qualityOptions.forEach((option) => {
        option.addEventListener('click', () => {
            changeQuality(option.dataset.value);
            if (qualityLabel) qualityLabel.textContent = option.dataset.label || 'Kalite';
            closeDropdowns();
        });
    });

    const savedSpeed = Number(localStorage.getItem('turtube-playback-speed') || '1');
    if ([0.25, 0.5, 0.75, 1, 1.25, 1.5, 2].includes(savedSpeed)) {
        player.playbackRate = savedSpeed;
        if (speedLabel) speedLabel.textContent = `${savedSpeed}x`;
    }

    speedOptions.forEach((option) => {
        option.addEventListener('click', () => {
            const speed = Number(option.dataset.value);
            player.playbackRate = speed;
            localStorage.setItem('turtube-playback-speed', String(speed));
            if (speedLabel) speedLabel.textContent = `${speed}x`;
            closeDropdowns();
            announce(`Oynatma hızı ${speed}x.`);
        });
    });

    captionsButton?.addEventListener('click', toggleCaptions);
    chapterSelect?.addEventListener('change', (event) => {
        seekToChapter(chapters.findIndex((chapter) => String(chapter.start) === event.target.value));
    });
    previousChapterButton?.addEventListener('click', () => {
        const current = currentChapterIndex();
        seekToChapter(player.currentTime > chapters[current].start + 2 ? current : current - 1);
    });
    nextChapterButton?.addEventListener('click', () => seekToChapter(currentChapterIndex() + 1));

    pipButton?.addEventListener('click', async () => {
        if (!document.pictureInPictureEnabled) {
            announce('Tarayıcınız resim içinde resim özelliğini desteklemiyor.');
            return;
        }

        try {
            if (document.pictureInPictureElement) {
                await document.exitPictureInPicture();
            } else {
                await player.requestPictureInPicture();
            }
        } catch {
            announce('Resim içinde resim başlatılamadı.');
        }
    });

    cinemaButton?.addEventListener('click', () => {
        const enabled = shell?.classList.toggle('turtube-cinema-mode');
        document.body.classList.toggle('overflow-hidden', Boolean(enabled));
        cinemaButton.setAttribute('aria-pressed', String(enabled));
        cinemaButton.dataset.active = String(enabled);
        if (cinemaLabel) cinemaLabel.textContent = enabled ? 'Sinema Modundan Çık' : 'Sinema Modu';
        announce(enabled ? 'Sinema modu açıldı.' : 'Sinema modu kapatıldı.');
    });

    document.addEventListener('fullscreenchange', () => {
        shell?.classList.toggle('is-fullscreen', Boolean(document.fullscreenElement));
    });

    const setMiniPlayer = (enabled) => {
        shell?.classList.toggle('turtube-mini-player', enabled);
        miniButton?.setAttribute('aria-pressed', String(enabled));
        if (miniButton) miniButton.dataset.active = String(enabled);
        announce(enabled ? 'Mini oynatıcı açıldı.' : 'Mini oynatıcı kapatıldı.');
    };

    miniButton?.addEventListener('click', () => setMiniPlayer(!shell?.classList.contains('turtube-mini-player')));
    miniCloseButton?.addEventListener('click', () => setMiniPlayer(false));

    playerFrame?.addEventListener('dblclick', (event) => {
        if (event.target !== player) return;
        event.preventDefault();
        const bounds = playerFrame.getBoundingClientRect();
        const seconds = event.clientX < bounds.left + bounds.width / 2 ? -10 : 10;
        player.currentTime = Math.max(0, Math.min(player.duration || Infinity, player.currentTime + seconds));
        announce(seconds < 0 ? '10 saniye geri sarıldı.' : '10 saniye ileri sarıldı.');
    });

    const autoplayEnabled = localStorage.getItem('turtube-autoplay-next') !== 'false';
    if (autoplayToggle) autoplayToggle.checked = autoplayEnabled;
    autoplayToggle?.addEventListener('change', () => {
        localStorage.setItem('turtube-autoplay-next', String(autoplayToggle.checked));
        if (!autoplayToggle.checked) clearAutoplayTimer();
    });

    const showEndScreen = () => {
        endScreen?.classList.remove('hidden');
        endScreen?.classList.add('flex');
        clearAutoplayTimer();

        if (!nextUrl || !autoplayToggle?.checked) return;

        let seconds = 5;
        const updateCountdown = () => {
            if (countdown) countdown.textContent = `${seconds} saniye içinde otomatik oynatılacak.`;
        };
        updateCountdown();
        autoplayTimer = window.setInterval(() => {
            seconds -= 1;
            if (seconds <= 0) {
                clearAutoplayTimer();
                window.location.assign(nextUrl);
                return;
            }
            updateCountdown();
        }, 1000);
    };

    player.addEventListener('ended', showEndScreen);
    player.addEventListener('play', () => {
        clearAutoplayTimer();
        endScreen?.classList.add('hidden');
        endScreen?.classList.remove('flex');
    });
    replayButton?.addEventListener('click', () => {
        player.currentTime = 0;
        player.play().catch(() => {});
    });

    document.addEventListener('keydown', (event) => {
        const tagName = event.target?.tagName;
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tagName) || event.target?.isContentEditable) return;

        const seekBy = (seconds) => {
            player.currentTime = Math.max(0, Math.min(player.duration || Infinity, player.currentTime + seconds));
        };

        switch (event.key.toLowerCase()) {
            case ' ':
                event.preventDefault();
                if (player.paused) player.play().catch(() => {}); else player.pause();
                break;
            case 'arrowleft':
                event.preventDefault();
                seekBy(-5);
                break;
            case 'arrowright':
                event.preventDefault();
                seekBy(5);
                break;
            case 'arrowup':
                event.preventDefault();
                player.volume = Math.min(1, player.volume + 0.1);
                break;
            case 'arrowdown':
                event.preventDefault();
                player.volume = Math.max(0, player.volume - 0.1);
                break;
            case 'm':
                player.muted = !player.muted;
                announce(player.muted ? 'Ses kapatıldı.' : 'Ses açıldı.');
                break;
            case 'c':
                if (captionsButton) toggleCaptions();
                break;
            case 'f':
                if (document.fullscreenElement) document.exitFullscreen(); else shell?.requestFullscreen?.();
                break;
            case 'n':
                if (chapters.length) seekToChapter(currentChapterIndex() + 1);
                break;
            case 'p':
                if (chapters.length) seekToChapter(currentChapterIndex() - 1);
                break;
            case 'escape':
                closeDropdowns();
                if (shell?.classList.contains('turtube-cinema-mode')) cinemaButton?.click();
                break;
        }
    });
}
