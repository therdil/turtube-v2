const uploadForm = document.querySelector('[data-upload-form]');

if (uploadForm) {
    const fileInput = uploadForm.querySelector('[data-upload-input]');
    const dropzone = uploadForm.querySelector('[data-upload-dropzone]');
    const fileName = uploadForm.querySelector('[data-upload-file]');
    const progress = uploadForm.querySelector('[data-upload-progress]');
    const progressBar = uploadForm.querySelector('[data-upload-progress-bar]');
    const progressPercent = uploadForm.querySelector('[data-upload-percent]');
    const progressStatus = uploadForm.querySelector('[data-upload-status]');
    const uploadNotice = uploadForm.querySelector('[data-upload-notice]');
    const submitButton = uploadForm.querySelector('button[type="submit"]');
    const shortToggle = uploadForm.querySelector('[data-short-toggle]');
    const contentTypeInput = uploadForm.querySelector('[data-content-type-input]');
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

    const showNotice = (message, type = 'success') => {
        if (!uploadNotice) return;

        uploadNotice.hidden = false;
        uploadNotice.textContent = message;
        uploadNotice.className = type === 'success'
            ? 'mt-4 rounded-xl border border-emerald-500/40 bg-emerald-500/10 p-4 text-sm font-medium text-emerald-200'
            : 'mt-4 rounded-xl border border-red-500/40 bg-red-500/10 p-4 text-sm font-medium text-red-200';
    };

    const resetSubmit = () => {
        submitButton?.removeAttribute('disabled');
        submitButton?.classList.remove('cursor-not-allowed', 'opacity-70');
    };

    const showFile = () => {
        const file = fileInput?.files?.[0];
        if (!file || !fileName) return;

        fileName.textContent = `${file.name} · ${(file.size / 1024 / 1024).toFixed(1)} MB`;
        fileName.classList.remove('hidden');
    };

    const syncContentType = () => {
        if (contentTypeInput) {
            contentTypeInput.value = shortToggle?.checked ? 'short' : 'video';
        }
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
    shortToggle?.addEventListener('change', syncContentType);
    syncContentType();
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

    uploadForm.addEventListener('submit', (event) => {
        if (!fileInput?.files?.length) return;

        event.preventDefault();
        uploadNotice?.setAttribute('hidden', 'hidden');
        submitButton?.setAttribute('disabled', 'disabled');
        submitButton?.classList.add('cursor-not-allowed', 'opacity-70');
        setProgress(0, 'Video yükleniyor');

        const request = new XMLHttpRequest();
        request.open('POST', uploadForm.action);
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        request.setRequestHeader('Accept', 'application/json');
        request.upload.addEventListener('progress', (progressEvent) => {
            if (!progressEvent.lengthComputable) return;
            setProgress(Math.round((progressEvent.loaded / progressEvent.total) * 100), 'Video yükleniyor');
        });
        request.addEventListener('load', () => {
            let payload = null;

            try {
                payload = JSON.parse(request.responseText);
            } catch {
                // Non-JSON responses are handled with a safe generic message below.
            }

            if (request.status >= 200 && request.status < 400 && payload?.success) {
                setProgress(100, 'Yükleme tamamlandı');
                showNotice(payload.message, 'success');
                window.setTimeout(() => window.location.assign(payload.redirect_url || uploadForm.action), 1800);
                return;
            }

            const validationMessages = payload?.errors
                ? Object.values(payload.errors).flat().join(' ')
                : null;
            showNotice(validationMessages || payload?.message || 'Video yüklenemedi. Lütfen bilgileri kontrol edip tekrar deneyin.', 'error');
            setProgress(0, 'Yükleme tamamlanamadı');
            resetSubmit();
        });
        request.addEventListener('error', () => {
            setProgress(0, 'Yükleme tamamlanamadı. Bağlantını kontrol edip tekrar dene.');
            showNotice('Video yüklenemedi. Bağlantını kontrol edip tekrar dene.', 'error');
            resetSubmit();
        });
        const formData = new FormData(uploadForm);
        formData.set('is_short', shortToggle?.checked ? '1' : '0');
        formData.set('content_type', shortToggle?.checked ? 'short' : 'video');

        request.send(formData);
    });
}
