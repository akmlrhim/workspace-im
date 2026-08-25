<?php

namespace App\Livewire\Concerns;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait StagesNewNoteAttachments
{
    /**
     * @var array<int, TemporaryUploadedFile>
     */
    public array $newNoteFiles = [];

    /**
     * @var array<int, array{url: string, label: string}>
     */
    public array $newNoteLinks = [];

    public string $newNoteLinkUrl = '';

    public string $newNoteLinkLabel = '';

    public function addStagedLink(): void
    {
        if (! $this->guardManage()) {
            return;
        }

        if (! $this->validateWithToast(
            ['newNoteLinkUrl' => 'required|url|max:2048', 'newNoteLinkLabel' => 'nullable|max:255'],
            [
                'newNoteLinkUrl.required' => 'URL wajib diisi.',
                'newNoteLinkUrl.url' => 'URL tidak valid.',
                'newNoteLinkUrl.max' => 'URL maksimal 2048 karakter.',
                'newNoteLinkLabel.max' => 'Label maksimal 255 karakter.',
            ]
        )) {
            return;
        }

        $url = trim($this->newNoteLinkUrl);
        $label = trim($this->newNoteLinkLabel) ?: $url;

        $this->newNoteLinks[] = ['url' => $url, 'label' => $label];
        $this->reset(['newNoteLinkUrl', 'newNoteLinkLabel']);
    }

    public function removeStagedLink(int $index): void
    {
        unset($this->newNoteLinks[$index]);
        $this->newNoteLinks = array_values($this->newNoteLinks);
    }

    public function removeStagedFile(int $index): void
    {
        unset($this->newNoteFiles[$index]);
        $this->newNoteFiles = array_values($this->newNoteFiles);
    }

    public function updatedNewNoteFiles(): void
    {
        if (! empty($this->newNoteFiles)) {
            $this->validateFiles($this->newNoteFiles);
        }
    }
}
