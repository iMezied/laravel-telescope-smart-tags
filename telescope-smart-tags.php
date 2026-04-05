<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Smart Tags
    |--------------------------------------------------------------------------
    | Set to false to completely disable all smart tag resolvers without
    | removing the package.
    */
    'enabled' => env('TELESCOPE_SMART_TAGS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Resolvers
    |--------------------------------------------------------------------------
    | Toggle individual tag resolvers on or off.
    */
    'resolvers' => [
        'http_status'   => true,
        'exceptions'    => true,
        'slow_requests' => true,
        'slow_queries'  => true,
        'route_groups'  => false, // opt-in — requires prefix_map config below
        'auth_context'  => false, // opt-in — adds auth:authenticated / auth:guest
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Status Tag Options
    |--------------------------------------------------------------------------
    | Controls which tag styles are applied to request entries.
    |
    | include_exact:    adds "http:422", "http:500", etc.
    | include_family:   adds "error:4xx", "error:5xx", "success:2xx", etc.
    | include_semantic: adds human aliases like "validation-failed", "rate-limited"
    | custom_map:       override or extend the default semantic alias map
    |                   [ status_code (int) => 'tag-name' ]
    */
    'http_status' => [
        'include_exact'    => true,
        'include_family'   => true,
        'include_semantic' => true,
        'custom_map'       => [
            // 418 => 'im-a-teapot',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exception Tag Options
    |--------------------------------------------------------------------------
    | include_class:       adds "exception:ValidationException"
    | include_family:      adds "family:validation", "family:database", etc.
    | custom_family_map:   map fully-qualified exception class => tag
    */
    'exceptions' => [
        'include_class'      => true,
        'include_family'     => true,
        'custom_family_map'  => [
            // 'App\Exceptions\PaymentGatewayException' => 'family:payment',
            // 'App\Exceptions\CarrierException'        => 'family:carrier',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Slow Request Thresholds (milliseconds)
    |--------------------------------------------------------------------------
    | Requests exceeding warn_ms get "slow:warn".
    | Requests exceeding critical_ms get "slow:critical".
    | Both also receive the generic "slow" tag.
    */
    'slow_requests' => [
        'warn_ms'     => env('TELESCOPE_SLOW_REQUEST_WARN_MS', 1000),
        'critical_ms' => env('TELESCOPE_SLOW_REQUEST_CRITICAL_MS', 3000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Slow Query Thresholds (milliseconds)
    |--------------------------------------------------------------------------
    | Database queries exceeding warn_ms get "slow-query:warn".
    | Queries exceeding critical_ms get "slow-query:critical".
    */
    'slow_queries' => [
        'warn_ms'     => env('TELESCOPE_SLOW_QUERY_WARN_MS', 500),
        'critical_ms' => env('TELESCOPE_SLOW_QUERY_CRITICAL_MS', 2000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Group Tags
    |--------------------------------------------------------------------------
    | Map URI prefixes or route name prefixes to tag names.
    | Longest matching prefix wins for URI matching.
    |
    | prefix_map:       URI prefix  => tag
    | route_name_map:   route name prefix => tag
    */
    'route_groups' => [
        'prefix_map' => [
            // 'api/v2'   => 'group:api-v2',
            // 'api/v1'   => 'group:api-v1',
            // 'api'      => 'group:api',
            // 'webhook'  => 'group:webhook',
            // 'admin'    => 'group:admin',
        ],
        'route_name_map' => [
            // 'api.'    => 'group:api',
            // 'admin.'  => 'group:admin',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Resolvers
    |--------------------------------------------------------------------------
    | Register your own resolver classes. Each must implement:
    | Imezied\TelescopeSmartTags\TagResolvers\TagResolverInterface
    |
    | They are resolved through the Laravel service container, so you can
    | inject dependencies into their constructors.
    */
    'custom_resolvers' => [
        // App\Telescope\CarrierTagResolver::class,
        // App\Telescope\TenantTagResolver::class,
    ],

];
