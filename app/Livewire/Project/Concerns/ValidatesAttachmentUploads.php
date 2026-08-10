<?php

namespace App\Livewire\Project\Concerns;

use Flux\Flux;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Shared attachment upload constraints, read from `config/erp.attachments`.
 *
 * Every layer that guards an upload — the browser check, Livewire's temporary
 * upload endpoint, and server-side validation — derives its limits from here,
 * so the three can never drift apart and reject a file only after the user has
 * already waited for a full upload.
 */
trait ValidatesAttachmentUploads
{
    /**
     * Maximum accepted upload size in kilobytes.
     */
    private function attachmentMaxSizeKb(): int
    {
        return (int) config('erp.attachments.max_size_kb', 10240);
    }

    /**
     * Maximum accepted upload size in bytes, for the browser-side check.
     */
    private function attachmentMaxSizeBytes(): int
    {
        return $this->attachmentMaxSizeKb() * 1024;
    }

    /**
     * Accepted file extensions, without a leading dot.
     *
     * @return array<int, string>
     */
    private function attachmentExtensions(): array
    {
        return (array) config('erp.attachments.extensions', []);
    }

    /**
     * Validation rules for a single uploaded attachment.
     */
    private function attachmentRules(): string
    {
        return implode('|', [
            'file',
            'max:'.$this->attachmentMaxSizeKb(),
            'extensions:'.implode(',', $this->attachmentExtensions()),
        ]);
    }

    /**
     * Validate a batch of uploaded files, showing a toast on the first failure.
     *
     * Files are validated through a dedicated validator rather than the
     * component's own `validate()`: a `foo.*` rule key only expands against a
     * real `foo` array in the validated data, so passing the wildcard key
     * straight through silently matches nothing and validates no file at all.
     *
     * @param  array<int, TemporaryUploadedFile>  $files
     */
    private function validateAttachmentFiles(array $files): bool
    {
        $megabytes = round($this->attachmentMaxSizeKb() / 1024);

        $validator = Validator::make(
            ['files' => $files],
            ['files.*' => $this->attachmentRules()],
            [
                'files.*.max' => "Ukuran file maksimal {$megabytes} MB.",
                'files.*.extensions' => 'Format file tidak didukung.',
                'files.*.file' => 'File gagal diunggah. Coba lagi.',
            ]
        );

        if ($validator->fails()) {
            Flux::toast($validator->errors()->first(), variant: 'danger');

            return false;
        }

        return true;
    }
}
