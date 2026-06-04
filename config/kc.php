<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'driver' => env('KC_STORAGE_DRIVER', 'local'),
        'disk'   => env('KC_STORAGE_DISK', 'local'),
        'path'   => env('KC_STORAGE_PATH', 'kc/attachments'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Attachment limits
    |--------------------------------------------------------------------------
    */
    'attachments' => [
        'max_file_size_mb'  => (int) env('KC_MAX_FILE_SIZE_MB', 50),
        'max_item_total_mb' => (int) env('KC_MAX_ITEM_TOTAL_MB', 200),

        'allowed_mimes' => [
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
            'video/mp4',
            'video/webm',
            'application/zip',
            'application/x-zip-compressed',
        ],

        'allowed_extensions' => [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'png', 'jpg', 'jpeg', 'gif', 'webp',
            'mp4', 'webm',
            'zip',
        ],
    ],

];
