<?php

return [
    'name' => 'KcItem',

    'search' => [
        // 'fulltext' = MySQL FULLTEXT (mặc định, không cần hạ tầng ngoài). Đổi sang driver
        // khác (VD 'meilisearch' khi cần scale) qua env — xem
        // Modules/KcItem/app/Contracts/KcItemSearchDriver.php + KcItemServiceProvider::register().
        'driver' => env('KC_SEARCH_DRIVER', 'fulltext'),
    ],
];
