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
    | Configuration for weather API integration for security operations.
    | Using OpenWeatherMap API for comprehensive weather data.
    |
    */
    'weather' => [
        'api_key' => env('WEATHER_API_KEY'),
        'enabled' => env('WEATHER_API_ENABLED', true),
        'base_url' => env('WEATHER_API_BASE_URL', 'https://api.openweathermap.org/data/2.5'),
        'provider' => env('WEATHER_PROVIDER', 'openweathermap'),
        'units' => env('WEATHER_DEFAULT_UNITS', 'metric'), // metric, imperial, kelvin
        'lang' => env('WEATHER_DEFAULT_LANG', 'ja'), // 日本語対応
        'cache_duration' => env('WEATHER_CACHE_DURATION', 1800), // 30分間キャッシュ
        
        // API エンドポイント設定
        'endpoints' => [
            'current' => '/weather',
            'forecast' => '/forecast',
            'onecall' => '/onecall',
            'air_pollution' => '/air_pollution',
            'geocoding' => 'http://api.openweathermap.org/geo/1.0/direct',
            'reverse_geocoding' => 'http://api.openweathermap.org/geo/1.0/reverse',
        ],
        
        // データ取得オプション
        'data_options' => [
            'include_alerts' => true,
            'include_minutely' => false,
            'include_hourly' => true,
            'include_daily' => true,
            'exclude' => 'minutely', // minutely, hourly, daily, alerts
        ],
        
        // 警備業務用リスクレベル設定
        'risk_thresholds' => [
            'temperature' => [
                'critical_low' => -10,  // -10°C以下で危険
                'high_low' => -5,       // -5°C以下で高リスク
                'medium_low' => 0,      // 0°C以下で中リスク
                'medium_high' => 30,    // 30°C以上で中リスク
                'high_high' => 35,      // 35°C以上で高リスク
                'critical_high' => 40,  // 40°C以上で危険
            ],
            'wind_speed' => [
                'medium' => 7,      // 7m/s以上で中リスク
                'high' => 10,       // 10m/s以上で高リスク
                'critical' => 15,   // 15m/s以上で危険
            ],
            'rainfall' => [
                'medium' => 5,      // 5mm/h以上で中リスク
                'high' => 10,       // 10mm/h以上で高リスク
                'critical' => 20,   // 20mm/h以上で危険
            ],
            'visibility' => [
                'critical' => 500,  // 500m以下で危険
                'high' => 1000,     // 1000m以下で高リスク
                'medium' => 5000,   // 5000m以下で中リスク
            ],
            'humidity' => [
                'high_dry' => 20,   // 20%以下で高リスク（乾燥）
                'high_humid' => 90, // 90%以上で高リスク（高湿度）
            ],
        ],
        
        // 警備業務適性判定設定
        'outdoor_work_thresholds' => [
            'temperature_min' => -10,
            'temperature_max' => 40,
            'rainfall_max' => 15,
            'wind_speed_max' => 20,
            'visibility_min' => 500,
            'exclude_weather_conditions' => [
                'Thunderstorm', // 雷雨
                'Tornado',      // 竜巻
                'Squall',       // スコール
            ],
        ],
        
        // 自動更新設定
        'auto_update' => [
            'enabled' => true,
            'interval_minutes' => 30,   // 30分毎に更新
            'retry_attempts' => 3,
            'retry_delay_seconds' => 60,
        ],
        
        // データ保持設定
        'data_retention' => [
            'current_data_days' => 7,   // 現在の天気データ保持期間
            'forecast_data_days' => 30, // 予報データ保持期間
            'cleanup_enabled' => true,
            'cleanup_schedule' => 'daily', // daily, weekly, monthly
        ],
        
        // 通知・アラート設定
        'notifications' => [
            'enabled' => true,
            'critical_alerts' => true,
            'high_risk_alerts' => true,
            'daily_summary' => true,
            'weather_change_alerts' => true,
            'channels' => [
                'email' => true,
                'slack' => false,
                'sms' => false,
            ],
        ],
        
        // ログ設定
        'logging' => [
            'enabled' => true,
            'level' => 'info', // debug, info, warning, error
            'api_calls' => true,
            'errors' => true,
            'performance' => false,
        ],
    ],
];
