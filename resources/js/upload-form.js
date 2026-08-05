const uploadForm = document.querySelector('[data-upload-form]');

if (uploadForm) {
    const fileInput = uploadForm.querySelector('[data-upload-input]');
    const dropzone = uploadForm.querySelector('[data-upload-dropzone]');
    const fileName = uploadForm.querySelector('[data-upload-file]');
    const progress = uploadForm.querySelector('[data-upload-progress]');
    const progressBar = uploadForm.querySelector('[data-upload-progress-bar]');
    const progressPercent = uploadForm.querySelector('[data-upload-percent]');
    const progressStatus = uploadForm.querySelector('[data-upload-status]');
    const submitButton = uploadForm.querySelector('button[type="submit"]');
    const thumbnailInput = uploadForm.querySelector('[data-thumbnail-input]');
    const thumbnailPreview = uploadForm.querySelector('[data-thumbnail-preview]');
    const thumbnailPlaceholder = uploadForm.querySelector('[data-thumbnail-placeholder]');
    const tagContainer = uploadForm.querySelector('[data-tag-container]');
    const tagInput = uploadForm.querySelector('[data-tag-input]');
    const tagList = uploadForm.querySelector('[data-tag-list]');
    const tags = new Set();

    const setProgress = (percent, status) => {
        progress?.classList.remove('hidden');
        if (progressBar) progressBar.style.width = `${percent}%`;
        if (progressPercent) progressPercent.textContent = `${percent}%`;
        if (progressStatus) progressStatus.textContent = status;
    };

    const showFile = () => {
        const file = fileInput?.files?.[0];
        if (!file || !fileName) return;

        fileName.textContent = `${file.name} · ${(file.size / 1024 / 1024).toFixed(1)} MB`;
        fileName.classList.remove('hidden');
    };

    const renderTags = () => {
        if (!tagList) return;

        tagList.replaceChildren();
        tags.forEach((tag) => {
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-1 rounded-full bg-red-600/15 px-2.5 py-1 text-xs font-semibold text-red-200';
            chip.textContent = tag;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'ml-1 text-red-200 transition hover:text-white';
            remove.setAttribute('aria-label', `${tag} etiketini kaldır`);
            remove.textContent = '×';
            remove.addEventListener('click', () => {
                tags.delete(tag);
                renderTags();
            });
            chip.append(remove);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'tags[]';
            input.value = tag;
            tagList.append(chip, input);
        });
    };

    const addTag = (value) => {
        const tag = value.trim().replace(/^#/, '');
        if (!tag || tags.size >= 12) return;

        tags.add(tag.slice(0, 50));
        renderTags();
    };

    try {
        JSON.parse(tagContainer?.dataset.initialTags || '[]').forEach(addTag);
    } catch {}

    fileInput?.addEventListener('change', showFile);
    dropzone?.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('border-red-500', 'bg-red-500/5');
    });
    dropzone?.addEventListener('dragleave', () => dropzone.classList.remove('border-red-500', 'bg-red-500/5'));
    dropzone?.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('border-red-500', 'bg-red-500/5');
        if (!event.dataTransfer?.files?.length || !fileInput) return;

        fileInput.files = event.dataTransfer.files;
        showFile();
    });
    dropzone?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            fileInput?.click();
        }
    });

    tagInput?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ',') return;
        event.preventDefault();
        addTag(tagInput.value);
        tagInput.value = '';
    });
    tagInput?.addEventListener('blur', () => {
        addTag(tagInput.value);
        tagInput.value = '';
    });

    thumbnailInput?.addEventListener('change', () => {
        const file = thumbnailInput.files?.[0];
        if (!file || !thumbnailPreview) return;

        const reader = new FileReader();
        reader.addEventListener('load', () => {
            thumbnailPreview.src = String(reader.result);
            thumbnailPreview.classList.remove('hidden');
            thumbnailPlaceholder?.classList.add('hidden');
        });
        reader.readAsDataURL(file);
    });

    uploadForm.addEventListener('submit', (event) => {
        if (!fileInput?.files?.length) return;

        event.preventDefault();
        submitButton?.setAttribute('disabled', 'disabled');
        submitButton?.classList.add('cursor-not-allowed', 'opacity-70');
        setProgress(0, 'Video yükleniyor');

        const request = new XMLHttpRequest();
        request.open('POST', uploadForm.action);
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        request.upload.addEventListener('progress', (progressEvent) => {
            if (!progressEvent.lengthComputable) return;
            setProgress(Math.round((progressEvent.loaded / progressEvent.total) * 100), 'Video yükleniyor');
        });
        request.addEventListener('load', () => {
            if (request.status >= 200 && request.status < 400) {
                setProgress(100, 'Yükleme tamamlandı, medya işleniyor');
                window.location.assign(request.responseURL || uploadForm.action);
                return;
            }

            window.location.assign(request.responseURL || uploadForm.action);
        });
        request.addEventListener('error', () => {
            setProgress(0, 'Yükleme tamamlanamadı. Bağlantını kontrol edip tekrar dene.');
            submitButton?.removeAttribute('disabled');
            submitButton?.classList.remove('cursor-not-allowed', 'opacity-70');
        });
        request.send(new FormData(uploadForm));
    });
}
