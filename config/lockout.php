<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Flag to enable/disable read-only mode from the .env file
    |--------------------------------------------------------------------------
    */
    'enabled' => env('APP_READ_ONLY', false),

    /*
    |--------------------------------------------------------------------------
    | Put the app in read-only mode but still allow users to login and view
    | Good if you want to give access to view a secure area but not alter data
    |--------------------------------------------------------------------------
    */
    'allow_login' => env('APP_READ_ONLY_LOGIN', false),

    /*
    |--------------------------------------------------------------------------
    | Path to your login/logout paths from the root, http://mysite.com/login
    | If allow_login = true
    |--------------------------------------------------------------------------
    */
    'login_path' => 'login',
    'logout_path' => 'logout',

    /*
    |--------------------------------------------------------------------------
    | The request types that you want to block
    |--------------------------------------------------------------------------
    */
    'locked_types' => [
        'post',
        'put',
        'patch',
        'delete',
    ],

    /*
    |--------------------------------------------------------------------------
    | The GET request, or paths that you want to prevent access to
    |--------------------------------------------------------------------------
    */
    'pages' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | White list certain request types to certain pages
    |--------------------------------------------------------------------------
    */
    'whitelist' => [
        // 'post' => 'password/confirm',
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist - Allow specific IP addresses to bypass lockout
    |--------------------------------------------------------------------------
    */
    'ip_whitelist' => env('LOCKOUT_IP_WHITELIST', ''),
    'ip_whitelist_array' => [
        // '127.0.0.1',
        // '192.168.1.0/24',
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Blacklist - Block specific IP addresses even if lockout is disabled
    |--------------------------------------------------------------------------
    */
    'ip_blacklist' => env('LOCKOUT_IP_BLACKLIST', ''),
    'ip_blacklist_array' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Role-Based Exceptions - Allow specific user roles to bypass lockout
    |--------------------------------------------------------------------------
    */
    'allowed_roles' => [
        // 'admin',
        // 'super-admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Response - Customize the response when lockout is active
    |--------------------------------------------------------------------------
    */
    'response_type' => env('LOCKOUT_RESPONSE_TYPE', 'abort'), // 'abort', 'view', 'json'
    'response_view' => 'lockout::maintenance',
    'response_message' => 'Application is currently in read-only mode.',
    'response_code' => 401, // HTTP_UNAUTHORIZED (backward compatible), use 503 for maintenance mode

    /*
    |--------------------------------------------------------------------------
    | Route Patterns - Whitelist routes by pattern or route name
    |--------------------------------------------------------------------------
    */
    'route_patterns' => [
        // 'api/*',
        // 'health',
    ],
    'route_names' => [
        // 'health.check',
    ],

    /*
    |--------------------------------------------------------------------------
    | API-Specific Handling
    |--------------------------------------------------------------------------
    */
    'api_enabled' => env('LOCKOUT_API_ENABLED', true),
    'api_response_type' => 'json', // 'json', 'abort'
    'api_response_message' => [
        'message' => 'Application is currently in read-only mode.',
        'status' => 'maintenance',
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Check Endpoint - Always accessible endpoint for monitoring
    |--------------------------------------------------------------------------
    */
    'health_check_enabled' => env('LOCKOUT_HEALTH_CHECK_ENABLED', true),
    'health_check_path' => env('LOCKOUT_HEALTH_CHECK_PATH', 'health'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache_enabled' => env('LOCKOUT_CACHE_ENABLED', true),
    'cache_key' => 'lockout.status',
    'cache_ttl' => 60, // seconds

    /*
    |--------------------------------------------------------------------------
    | Event System
    |--------------------------------------------------------------------------
    */
    'fire_events' => env('LOCKOUT_FIRE_EVENTS', true),
];
