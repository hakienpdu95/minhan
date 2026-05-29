<?php
return [
    'queue'                 => env('WORKFLOW_QUEUE', 'workflows'),
    'retain_execution_days' => env('WORKFLOW_RETAIN_DAYS', 60),
    'webhook_timeout'       => env('WORKFLOW_WEBHOOK_TIMEOUT', 15),
    'webhook_max_retries'   => env('WORKFLOW_WEBHOOK_RETRIES', 2),
    'allow_manual_trigger'  => env('WORKFLOW_ALLOW_MANUAL', true),
    'meta_cache_ttl'        => env('WORKFLOW_META_CACHE_TTL', 600),
    'meta_cache_version'    => env('WORKFLOW_META_VERSION', 1),
];
