<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Maps API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Google Maps API settings for location
    | tracking, route optimization, and map display functionality.
    |
    */
    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'enabled' => env('GOOGLE_MAPS_ENABLED', true),
        'default_lat' => env('GOOGLE_MAPS_DEFAULT_LAT', 35.6762), // 東京駅
        'default_lng' => env('GOOGLE_MAPS_DEFAULT_LNG', 139.6503),
        'default_zoom' => env('GOOGLE_MAPS_DEFAULT_ZOOM', 12),
        'options' => [
            'zoom_control' => true,
            'street_view_control' => true,
            'fullscreen_control' => true,
            'map_type_control' => true,
            'rotate_control' => false,
            'scale_control' => true,
        ],
        'styles' => [
            // カスタムマップスタイル（警備業界向け）
            'security_theme' => [
                [
                    'featureType' => 'poi.business',
                    'stylers' => [
                        ['visibility' => 'on'],
                        ['color' => '#0066cc'],
                    ],
                ],
                [
                    'featureType' => 'poi.government',
                    'stylers' => [
                        ['visibility' => 'on'],
                        ['color' => '#cc0000'],
                    ],
                ],
                [
                    'featureType' => 'poi.medical',
                    'stylers' => [
                        ['visibility' => 'on'],
                        ['color' => '#00cc66'],
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Weather API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for weather API integration for security operations
    |
    */
    'weather' => [
        'api_key' => env('WEATHER_API_KEY'),
        'enabled' => env('WEATHER_API_ENABLED', false),
        'provider' => env('WEATHER_PROVIDER', 'openweathermap'),
        'cache_minutes' => env('WEATHER_CACHE_MINUTES', 30),
    ],
];
