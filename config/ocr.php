<?php

return [

    // Driver cho generic OCR (Facade Ocr::extract / Ocr::readCccd)
    // Hỗ trợ: 'tesseract' (self-hosted, miễn phí), hoặc driver tuỳ chỉnh
    'driver' => env('OCR_DRIVER', 'tesseract'),

    'drivers' => [
        'tesseract' => [
            'langs'        => ['vie', 'eng'],
            'psm'          => 6,    // Assume a single uniform block of text (mặc định)
            'oem'          => 3,    // Default OCR engine (LSTM + Legacy)
            // Custom tessconf files: disable dictionary penalties (load_system_dawg=0 etc.)
            // → Tesseract không penalize ký tự không có trong từ điển tiếng Việt (số CCCD, tên riêng)
            'tessconf'     => storage_path('app/tessconf/cccd.conf'),
            'tessconf_tsv' => storage_path('app/tessconf/cccd_tsv.conf'),
            // DPI hint: KHÔNG bật mặc định — thay đổi DPI làm Tesseract đọc sai line segmentation
            // trên ảnh CCCD đã scale 2× (effective ~600 DPI).
            // Bật lại nếu chụp nguyên gốc không scale, ảnh thực sự 300 DPI.
            // 'dpi' => 300,
        ],
    ],

    // Chiến lược preprocessing mặc định (dùng bởi ImagePreprocessor::prepare())
    // Để thay đổi cho từng lần chạy: truyền strategy vào prepareWithStrategy()
    'preprocessing' => [
        'strategy'   => 'standard',   // 'standard' | 'high_contrast' | 'mrz'
        'greyscale'  => true,
        'scale'      => 2,             // phóng to 2× → tăng pixel density cho Tesseract
        'brightness' => 10,
        'contrast'   => 60,            // 60 tốt nhất cho CCCD: xuyên hologram mà không mất text tên
        'sharpen'    => 0,             // tắt — sharpen tăng noise trên ảnh hologram
    ],

    // Chiến lược multi-pass mặc định (dùng bởi OcrManager::extractBest / readCccdBest)
    'multipass' => [
        'strategies' => ['standard', 'high_contrast'],
        'psm_modes'  => [6, 3],
    ],

];
