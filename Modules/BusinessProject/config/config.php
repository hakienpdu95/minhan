<?php

return [
    'name' => 'BusinessProject',

    // Phần 9 spec — "Bypass Diagnosis ở Phase 1" (Phase 1 MVP, đã hoàn thành). Phase 2: Diagnosis
    // Workspace thật + Approval R3 đã build — flag bật `true`, CheckStageGateEligibilityHandler
    // nhánh 'diagnosis' giờ kiểm tra Diagnosis Report thật đã approved (không đổi cấu trúc match(),
    // đúng thiết kế đã chuẩn bị trước ở Phase 1).
    'stage_gates' => [
        'diagnosis' => [
            'enforced' => true,
        ],
    ],

    // Rule R4 — chữ ký nội bộ khi Confirmed. 'internal_rsa' (mặc định, không cần hạ tầng ngoài)
    // hoặc provider CA thật sau này. Xem Modules/BusinessProject/app/Contracts/DeliverableSignatureProvider.php.
    'signature' => [
        'provider' => env('BCOS_SIGNATURE_PROVIDER', 'internal_rsa'),
    ],
];
