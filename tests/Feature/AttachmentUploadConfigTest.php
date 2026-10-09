<?php

// Livewire stages every upload on a temporary disk before the file is moved to
// the permanent one. The S3 driver refuses multi-file requests, so the staged
// disk has to stay on a driver that supports them. These tests pin the config
// relationship down so a stray `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3` cannot
// silently break every attachment input in the app again.

test('the livewire temporary upload disk supports multi-file uploads', function () {
    $tempDisk = config('livewire.temporary_file_upload.disk');
    $driver = config("filesystems.disks.{$tempDisk}.driver");

    expect($tempDisk)->not->toBeNull()
        ->and($driver)->not->toBe('s3');
});

test('the livewire temporary upload disk follows the app filesystem disk by default', function () {
    // When LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK is unset the config falls back to
    // FILESYSTEM_DISK, so staging and the permanent store can never drift apart
    // just because one env key was forgotten.
    $config = file_get_contents(config_path('livewire.php'));

    expect($config)
        ->toContain("env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK', env('FILESYSTEM_DISK', 'local'))")
        ->and($config)->toContain("'local'");
});

test('the temporary upload rule never rejects what the attachment config allows', function () {
    $rules = config('livewire.temporary_file_upload.rules');
    $maxRule = collect($rules)->first(fn (string $rule) => str_starts_with($rule, 'max:'));

    expect($maxRule)->not->toBeNull();

    $tempMaxKb = (int) substr($maxRule, 4);
    $attachmentMaxKb = (int) config('project.attachments.max_size_kb');

    expect($tempMaxKb)->toBeGreaterThanOrEqual($attachmentMaxKb);
});
