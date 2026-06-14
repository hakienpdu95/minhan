<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default OTP Driver
    |--------------------------------------------------------------------------
    | Supported: "log", "null", "zbs_zns"
    |
    | log     — writes to Laravel log (good for local dev + CI)
    | null    — silent success (unit tests)
    | zbs_zns — Zalo ZNS Template Message API (production)
    |
    | Switch via env: OTP_CHANNEL_DRIVER=zbs_zns
    */

    'driver' => env('OTP_CHANNEL_DRIVER', 'log'),

    'drivers' => [

        'log' => [],

        'null' => [],

        'zbs_zns' => [
            'app_id'     => env('ZBS_APP_ID'),
            'app_secret' => env('ZBS_APP_SECRET'),

            /*
            | ZNS template for OTP verification.
            | Template must be pre-approved in Zalo Developer Console.
            | template_params maps internal keys to the actual param names
            | configured in the approved ZNS template.
            */
            'template_id' => env('ZBS_OTP_TEMPLATE_ID'),

            'template_params' => [
                'otp'         => env('ZBS_OTP_PARAM_OTP', 'otp'),
                'expire_time' => env('ZBS_OTP_PARAM_EXPIRE', 'expire_time'),
            ],

            /*
            | Endpoints — override only if Zalo changes them
            */
            'endpoints' => [
                'token'    => 'https://oauth.zaloapp.com/v4/oa/access_token',
                'template' => 'https://business.openapi.zalo.me/message/template',
            ],
        ],

    ],

];
