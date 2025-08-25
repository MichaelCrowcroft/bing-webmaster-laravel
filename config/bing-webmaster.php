<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bing Webmaster API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Bing Webmaster API package.
    | You can set your OAuth access token and other settings here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Configuration
    |--------------------------------------------------------------------------
    |
    | This package doesn't manage access tokens automatically. Instead, you should
    | pass the access token explicitly when creating instances or making requests.
    | This allows for multi-tenant applications where different users have different tokens.
    |
    */
    'access_token' => null, // Not used - tokens should be passed explicitly

    /*
    |--------------------------------------------------------------------------
    | Default Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests. You can adjust this based
    | on your needs. A higher timeout may be needed for large data queries.
    |
    */
    'timeout' => env('BING_WEBMASTER_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Default Retry Attempts
    |--------------------------------------------------------------------------
    |
    | The number of times to retry failed API requests before giving up.
    | This can help with transient network issues or temporary API errors.
    |
    */
    'retry_attempts' => env('BING_WEBMASTER_RETRY_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | Default API Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for various API operations. These can be overridden
    | when making individual requests.
    |
    */
    'api' => [
        'base_url' => env('BING_WEBMASTER_BASE_URL', 'https://www.bing.com/webmaster/api.svc/json'),

        'rank_and_traffic' => [
            'default_date_range' => [
                'start_date' => '-30 days',
                'end_date' => 'yesterday',
            ],
            'default_aggregation' => 'daily', // daily, weekly, monthly
        ],

        'keyword_stats' => [
            'default_date_range' => [
                'start_date' => '-30 days',
                'end_date' => 'yesterday',
            ],
            'default_limit' => 1000,
        ],

        'page_stats' => [
            'default_date_range' => [
                'start_date' => '-30 days',
                'end_date' => 'yesterday',
            ],
            'default_limit' => 1000,
        ],

        'query_stats' => [
            'default_date_range' => [
                'start_date' => '-30 days',
                'end_date' => 'yesterday',
            ],
            'default_limit' => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Configure caching for API responses to reduce API calls and improve
    | performance. Cache results are stored according to your Laravel
    | cache configuration.
    |
    */
    'cache' => [
        'enabled' => env('BING_WEBMASTER_CACHE_ENABLED', false),
        'ttl' => env('BING_WEBMASTER_CACHE_TTL', 3600), // 1 hour in seconds
        'prefix' => env('BING_WEBMASTER_CACHE_PREFIX', 'bing_webmaster_'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Enable debug mode to log API requests and responses. This is useful
    | for development and troubleshooting.
    |
    */
    'debug' => env('BING_WEBMASTER_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting to prevent hitting API limits. These are
    | applied per minute.
    |
    */
    'rate_limiting' => [
        'enabled' => env('BING_WEBMASTER_RATE_LIMITING_ENABLED', true),
        'max_requests_per_minute' => env('BING_WEBMASTER_RATE_LIMIT', 60),
    ],
];
