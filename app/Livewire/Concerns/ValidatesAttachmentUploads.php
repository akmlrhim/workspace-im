<?php

namespace App\Livewire\Concerns;

use Flux\Flux;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait ValidatesAttachmentUploads
{
    private function attachmentMaxSizeKb(): int
    {
        return (int) config('project.attachments.max_size_kb', 10240);
    }

    private function attachmentMaxSizeBytes(): int
    {
        return $this->attachmentMaxSizeKb() * 1024;
    }

    /**
     * @return array<int, string>
     */
    private function attachmentExtensions(): array
    {
        return (array) config('project.attachments.extensions', []);
    }

    private function attachmentRules(): string
    {
        return implode('|', [
            'file',
            'max:'.$this->attachmentMaxSizeKb(),
            'extensions:'.implode(',', $this->attachmentExtensions()),
        ]);
    }

    /**
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
