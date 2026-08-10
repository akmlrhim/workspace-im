// Client-side companion for Livewire's native file uploads.
//
// Livewire dispatches bubbling DOM events on a `wire:model` file input while an
// upload is in flight: `livewire-upload-start`, `livewire-upload-progress`,
// `livewire-upload-finish` and `livewire-upload-error`. A wrapper using the
// `fileUpload` Alpine component watches those events to render real-time
// progress and surface upload failures so users never stare at a silent
// spinner. It also validates files before they reach the server's temporary
// upload endpoint, so oversized / unsupported files fail fast instead of
// wasting a full upload first.
//
// Limits come from meta tags rendered by partials/head.blade.php, which read
// `config/erp.attachments` — the same config server-side validation uses. That
// keeps the browser check and the server from ever disagreeing, which is what
// previously let a file upload completely and only then be rejected.

const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content;

const defaultMaxSize = () => Number(meta("upload-max-size") ?? 0);

const defaultExtensions = () =>
    (meta("upload-extensions") ?? "")
        .split(",")
        .map((extension) => extension.trim().toLowerCase())
        .filter(Boolean);

const formatMegabytes = (bytes) => {
    const megabytes = bytes / (1024 * 1024);

    return Number.isInteger(megabytes) ? `${megabytes}` : megabytes.toFixed(1);
};

document.addEventListener('alpine:init', () => {
    window.Alpine.data('fileUpload', (config = {}) => ({
        // Max file size in bytes; 0 disables the client-side size check.
        maxSize: config.maxSize ?? defaultMaxSize(),

        // Allowed extensions (without the dot).
        allowedTypes: config.extensions ?? defaultExtensions(),

        uploading: false,
        progress: 0,
        error: '',
        _clearTimer: null,

        // Make sure a previous error does not linger into a new upload attempt.
        resetFeedback() {
            this.progress = 0;
            this.error = '';
            clearTimeout(this._clearTimer);
        },

        showError(message) {
            this.error = message;

            clearTimeout(this._clearTimer);
            this._clearTimer = setTimeout(() => {
                this.error = '';
            }, 8000);
        },

        // Runs in `@change.capture` BEFORE Livewire's own change handler, so the
        // filtered file list is already in place when Livewire starts uploading.
        validate(event) {
            const input = event.target;
            const files = Array.from(input.files || []);

            if (files.length === 0) {
                return;
            }

            this.resetFeedback();

            // Prefer the input's own `accept` when it narrows the shared list
            // (an image-only picker, say); otherwise fall back to the config.
            const accepted = (input.accept || '')
                .split(',')
                .map((type) => type.trim().replace(/^\./, '').toLowerCase())
                .filter(Boolean);

            const allowedTypes = accepted.length > 0 ? accepted : this.allowedTypes;

            const oversized = [];
            const unsupported = [];
            const allowed = [];

            files.forEach((file) => {
                const extension = (file.name.split('.').pop() || '').toLowerCase();

                if (allowedTypes.length > 0 && !allowedTypes.includes(extension)) {
                    unsupported.push(file.name);

                    return;
                }

                if (this.maxSize > 0 && file.size > this.maxSize) {
                    oversized.push(file.name);

                    return;
                }

                allowed.push(file);
            });

            // Separate messages: "too big" and "wrong format" need different
            // fixes, and a merged message leaves the user guessing which it was.
            const problems = [];

            if (oversized.length === 1) {
                problems.push(`"${oversized[0]}" melebihi batas ${formatMegabytes(this.maxSize)} MB.`);
            } else if (oversized.length > 1) {
                problems.push(`${oversized.length} file melebihi batas ${formatMegabytes(this.maxSize)} MB.`);
            }

            if (unsupported.length === 1) {
                problems.push(`Format "${unsupported[0]}" tidak didukung.`);
            } else if (unsupported.length > 1) {
                problems.push(`${unsupported.length} file memakai format yang tidak didukung.`);
            }

            if (problems.length > 0) {
                this.showError(problems.join(' '));
            }

            if (allowed.length === 0) {
                // Clearing the input stops Livewire uploading anything, and lets
                // the same file be re-picked after the user fixes it.
                input.value = '';

                return;
            }

            if (allowed.length === files.length) {
                return;
            }

            // Only a subset was rejected - keep the valid files for upload.
            try {
                const transfer = new DataTransfer();
                allowed.forEach((file) => transfer.items.add(file));
                input.files = transfer.files;
            } catch (exception) {
                input.value = '';
                this.showError('File tidak dapat diproses browser. Coba unggah satu per satu.');
            }
        },
    }));
});
