<?php

return [
    'domain' => env('HORIZON_DOMAIN'),
    'path' => env('HORIZON_PATH', 'horizon'),
    'use' => 'default',
    'prefix' => env('HORIZON_PREFIX', env('APP_NAME', 'laravel').'_horizon:'),
    'middleware' => ['web'],
    'waits' => [
        'redis:printing' => 60,
        'redis:default' => 60,
    ],
    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],
    'defaults' => [
        'supervisor-printing' => [
            'connection' => 'redis',
            'queue' => ['printing', 'default'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 2,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 128,
            'tries' => 3,
            'timeout' => 90,
            'nice' => 0,
        ],
    ],
    'environments' => [
        'production' => [
            'supervisor-printing' => [
                'maxProcesses' => 4,
            ],
        ],
        'local' => [
            'supervisor-printing' => [
                'maxProcesses' => 2,
            ],
        ],
    ],
];
