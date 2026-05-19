<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | CoreWatch Dashboard Activation
    |--------------------------------------------------------------------------
    |
    | Enable or disable the entire CoreWatch server monitoring suite.
    |
    */
    'enabled' => env('COREWATCH_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Dashboard URI Route Path
    |--------------------------------------------------------------------------
    |
    | The path where the CoreWatch dashboard will be accessible.
    |
    */
    'path' => env('COREWATCH_PATH', 'corewatch'),

    /*
    |--------------------------------------------------------------------------
    | Allowed Environments
    |--------------------------------------------------------------------------
    |
    | The environments in which CoreWatch is permitted to run.
    | Useful for restricting access in production if needed.
    |
    */
    'environments' => [
        'local',
        'staging',
        'production',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Define the middleware stack to protect the CoreWatch routes.
    | By default, we recommend utilizing 'web' and 'auth'. You can also
    | create your own custom authorization middleware.
    |
    */
    'middleware' => [
        'web',
        // 'auth', // Uncomment or add custom auth gates in production
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization Gate / Callback
    |--------------------------------------------------------------------------
    |
    | If you want to use a custom authorization gate callback, you can specify
    | a class and method, or a Laravel Gate name. This offers fine-grained access.
    | Set to null to rely purely on middleware.
    |
    */
    'gate' => env('COREWATCH_GATE', null),

    /*
    |--------------------------------------------------------------------------
    | UI Refresh Interval
    |--------------------------------------------------------------------------
    |
    | Asynchronous polling interval in milliseconds for the AlpineJS frontend
    | to fetch real-time server metrics from the internal API.
    |
    */
    'refresh_interval' => env('COREWATCH_REFRESH_INTERVAL', 5000),

    /*
    |--------------------------------------------------------------------------
    | Enabled Widgets & Modules
    |--------------------------------------------------------------------------
    |
    | Toggle individual widget panels on the dashboard UI.
    |
    */
    'widgets' => [
        'cpu' => true,
        'ram' => true,
        'disk' => true,
        'services' => true,
        'logs' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Server Alerting Thresholds
    |--------------------------------------------------------------------------
    |
    | Set the limits for hardware resources. The health cron job evaluates
    | these parameters and triggers alerts if they are breached.
    |
    */
    'thresholds' => [
        'cpu' => (float) env('COREWATCH_CPU_THRESHOLD', 85.0),  // in %
        'ram' => (float) env('COREWATCH_RAM_THRESHOLD', 90.0),  // in %
        'disk' => (float) env('COREWATCH_DISK_THRESHOLD', 90.0), // in %
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notification channels for alerts when resource thresholds are
    | exceeded. Supports Slack Webhooks and Telegram Bot API.
    |
    */
    'notifications' => [
        'channels' => ['slack', 'telegram'], // 'slack', 'telegram'

        'slack' => [
            'webhook_url' => env('COREWATCH_SLACK_WEBHOOK_URL'),
            'channel' => env('COREWATCH_SLACK_CHANNEL', '#devops-alerts'),
        ],

        'telegram' => [
            'bot_token' => env('COREWATCH_TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('COREWATCH_TELEGRAM_CHAT_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Safe System Service Controls
    |--------------------------------------------------------------------------
    |
    | CoreWatch lets you execute specific pre-configured administrative commands
    | safely via the UI. Only commands defined here can be run.
    | To protect your server, DO NOT accept raw inputs from the request.
    |
    */
    'services' => [
        'php_queue' => [
            'name' => 'Artisan Queue Restart',
            'command' => 'php artisan queue:restart',
            'type' => 'artisan', // 'artisan', 'shell'
        ],
        'cache_clear' => [
            'name' => 'Clear Application Cache',
            'command' => 'cache:clear',
            'type' => 'artisan',
        ],
        'redis_flush' => [
            'name' => 'Flush Redis Cache',
            'command' => 'redis-cli flushall',
            'type' => 'shell',
        ],
        'supervisor_restart' => [
            'name' => 'Restart Supervisor Services',
            'command' => 'supervisorctl restart all',
            'type' => 'shell',
        ],
        'opcache_reset' => [
            'name' => 'Reset PHP OPcache',
            'command' => 'php -r "opcache_reset();"',
            'type' => 'shell',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log File Viewer Configuration
    |--------------------------------------------------------------------------
    |
    | Define the log files available to view and parse.
    | Supports multiple types: Laravel, Nginx Access, Nginx Error, etc.
    |
    */
    'logs' => [
        'max_lines_per_page' => 100, // Safe streaming limit
        'files' => [
            'laravel' => [
                'name' => 'Laravel Application Log',
                'path' => storage_path('logs/laravel.log'),
                'type' => 'laravel',
            ],
            'nginx_access' => [
                'name' => 'Nginx Access Log',
                'path' => env('COREWATCH_NGINX_ACCESS_LOG', '/var/log/nginx/access.log'),
                'type' => 'nginx_access',
            ],
            'nginx_error' => [
                'name' => 'Nginx Error Log',
                'path' => env('COREWATCH_NGINX_ERROR_LOG', '/var/log/nginx/error.log'),
                'type' => 'nginx_error',
            ],
            'apache_access' => [
                'name' => 'Apache Access Log',
                'path' => env('COREWATCH_APACHE_ACCESS_LOG', '/var/log/apache2/access.log'),
                'type' => 'apache_access',
            ],
            'apache_error' => [
                'name' => 'Apache Error Log',
                'path' => env('COREWATCH_APACHE_ERROR_LOG', '/var/log/apache2/error.log'),
                'type' => 'apache_error',
            ],
        ],
    ],
];
