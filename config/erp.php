<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Attachment Uploads
    |--------------------------------------------------------------------------
    |
    | Single source of truth for every attachment upload in the app: task
    | attachments, checklist item attachments, and list note attachments.
    | The client-side check, Livewire's temporary upload endpoint
    | (config/livewire.php) and server-side validation all read these values,
    | so a file that passes the browser check can never be rejected after a
    | full upload has already been paid for.
    |
    | Validation uses `extensions` rather than `mimes` on purpose. `mimes`
    | sniffs file contents, and Office formats (docx/xlsx/pptx) sniff as
    | application/zip while csv sniffs as text/plain, which rejected valid
    | files intermittently. `extensions` checks the user-assigned extension,
    | which is also what determines the stored filename on disk.
    |
    */
    'attachments' => [
        // Shared with config/livewire.php via the same env key, so the temporary
        // upload endpoint and final validation always agree.
        'max_size_kb' => (int) env('ATTACHMENT_MAX_SIZE_KB', 10240),

        'extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'csv', 'zip',
        ],
    ],

    'modules' => [
        'project' => [
            'name' => 'Project',
            'icon' => 'clipboard-document-list',
            'url' => '/general-taskboard',
            'color' => 'indigo',
            'description' => 'Kelola space, task list, dan tugas tim Anda.',
            'allowed_positions' => [],
            'sidebar' => [
                ['name' => 'General', 'icon' => 'star', 'url' => '/general-taskboard', 'active' => 'general-taskboard*', 'badge' => null],
                ['name' => 'My Tasks', 'icon' => 'clipboard-document-check', 'url' => '/my-tasks', 'active' => 'my-tasks*', 'badge' => null],
                ['name' => 'Workload', 'icon' => 'chart-bar-square', 'url' => '/workload', 'active' => 'workload*', 'badge' => null],
            ],
        ],

        'users' => [
            'name' => 'Users',
            'icon' => 'users',
            'url' => '/users',
            'color' => 'sky',
            'description' => 'Kelola data admin dan permission sistem.',
            'allowed_positions' => [],
            'required_role' => 'super_user',
            'sidebar' => [
                ['name' => 'Users', 'icon' => 'users', 'url' => '/users', 'active' => 'users*', 'badge' => null],
            ],
        ],
    ],
];
