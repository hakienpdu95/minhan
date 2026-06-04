<?php

return [
    'attachments' => [
        'max_size_kb'      => env('SOP_ATTACHMENT_MAX_KB', 20480), // 20 MB default
        'storage_disk'     => env('SOP_ATTACHMENT_DISK', 'local'),
        'storage_prefix'   => 'sop-attachments',
        'allowed_mimes'    => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/png',
            'image/jpeg',
            'image/gif',
            'image/webp',
            'text/plain',
            'application/zip',
            'application/x-zip-compressed',
        ],
        'allowed_extensions' => [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'png', 'jpg', 'jpeg', 'gif', 'webp',
            'txt', 'zip',
        ],
    ],
];
