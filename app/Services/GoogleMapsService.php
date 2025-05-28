<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Google Maps API統合サービス
 * 
 * Google Maps APIとの連携機能を提供
 * ジオコーディング、ルート計算、距離行列計算、Places API等を統合管理
 */
class GoogleMapsService
{
    /**
     * Google Maps APIキー
     */
    private string $apiKey;

    /**
     * APIベースURL
     */
    private string $baseUrl = 'https://maps.googleapis.com/maps/api';

    /**
     * キャッシュ有効期間（秒）
     */
    private int $cacheTtl = 3600; // 1時間

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
        
        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('Google Maps API key is not configured');
        }
    }

    /**
     * Google Maps APIが有効かどうかを判定
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return config('services.google_maps.enabled', false) && !empty($this->apiKey);
    }

    /*
    |--------------------------------------------------------------------------
    | ジオコーディング機能
    |--------------------------------------------------------------------------
    */

    /**
     * 住所から座標を取得（ジオコーディング）
     * 
     * @param string $address
     * @param string $language
     * @param string $region
     * @return array|null
     */
    public function geocodeAddress(string $address, string $language = 'ja', string $region = 'jp'): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('Google Maps API is disabled');
            return null;
        }

        $cacheKey = "geocode:" . md5($address . $language . $region);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($address, $language, $region) {
            try {
                $response = Http::get("{$this->baseUrl}/geocode/json", [
                    'address' => $address,
                    'language' => $language,
                    'region' => $region,
                    'key' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'OK' && !empty($data['results'])) {
                        $result = $data['results'][0];
                        
                        return [
                            'latitude' => $result['geometry']['location']['lat'],
                            'longitude' => $result['geometry']['location']['lng'],
                            'formatted_address' => $result['formatted_address'],
                            'place_id' => $result['place_id'],
                            'address_components' => $result['address_components'] ?? [],
                            'location_type' => $result['geometry']['location_type'] ?? 'APPROXIMATE',
                            'viewport' => $result['geometry']['viewport'] ?? null,
                        ];
                    }
                }
                
                Log::error('Geocoding failed', [
                    'address' => $address,
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'error_message' => $data['error_message'] ?? 'No error message'
                ]);
                
                return null;
                
            } catch (\Exception $e) {
                Log::error('Geocoding API error', [
                    'address' => $address,
                    'error' => $e->getMessage()
                ]);
                
                return null;
            }
        });
    }

    /**
     * 座標から住所を取得（逆ジオコーディング）
     * 
     * @param float $latitude
     * @param float $longitude
     * @param string $language
     * @return array|null
     */
    public function reverseGeocode(float $latitude, float $longitude, string $language = 'ja'): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('Google Maps API is disabled');
            return null;
        }

        $cacheKey = "reverse_geocode:" . md5($latitude . $longitude . $language);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($latitude, $longitude, $language) {
            try {
                $response = Http::get("{$this->baseUrl}/geocode/json", [
                    'latlng' => "{$latitude},{$longitude}",
                    'language' => $language,
                    'key' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'OK' && !empty($data['results'])) {
                        $result = $data['results'][0];
                        
                        return [
                            'formatted_address' => $result['formatted_address'],
                            'place_id' => $result['place_id'],
                            'address_components' => $result['address_components'] ?? [],
                            'location_type' => $result['geometry']['location_type'] ?? 'APPROXIMATE',
                            'all_results' => $data['results'], // すべての結果を含む
                        ];
                    }
                }
                
                Log::error('Reverse geocoding failed', [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'status' => $data['status'] ?? 'UNKNOWN'
                ]);
                
                return null;
                
            } catch (\Exception $e) {
                Log::error('Reverse geocoding API error', [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'error' => $e->getMessage()
                ]);
                
                return null;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 距離・ルート計算機能
    |--------------------------------------------------------------------------
    */

    /**
     * 2点間の距離と時間を計算
     * 
     * @param array $origin ['lat' => float, 'lng' => float]
     * @param array $destination ['lat' => float, 'lng' => float]
     * @param string $mode driving, walking, transit, bicycling
     * @param array $options
     * @return array|null
     */
    public function calculateDistance(array $origin, array $destination, string $mode = 'driving', array $options = []): ?array
    {
        if (!$this->isEnabled()) {
            return $this->calculateHaversineDistance($origin, $destination);
        }

        $cacheKey = "distance:" . md5(json_encode([$origin, $destination, $mode, $options]));
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($origin, $destination, $mode, $options) {
            try {
                $params = [
                    'origins' => "{$origin['lat']},{$origin['lng']}",
                    'destinations' => "{$destination['lat']},{$destination['lng']}",
                    'mode' => $mode,
                    'language' => 'ja',
                    'units' => 'metric',
                    'key' => $this->apiKey,
                ];

                // オプション追加
                if (!empty($options['avoid'])) {
                    $params['avoid'] = implode('|', $options['avoid']);
                }
                if (!empty($options['departure_time'])) {
                    $params['departure_time'] = $options['departure_time'];
                }

                $response = Http::get("{$this->baseUrl}/distancematrix/json", $params);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'OK' && 
                        !empty($data['rows'][0]['elements'][0]) &&
                        $data['rows'][0]['elements'][0]['status'] === 'OK') {
                        
                        $element = $data['rows'][0]['elements'][0];
                        
                        return [
                            'distance' => [
                                'text' => $element['distance']['text'],
                                'value' => $element['distance']['value'], // メートル
                            ],
                            'duration' => [
                                'text' => $element['duration']['text'],
                                'value' => $element['duration']['value'], // 秒
                            ],
                            'mode' => $mode,
                            'origin_address' => $data['origin_addresses'][0] ?? null,
                            'destination_address' => $data['destination_addresses'][0] ?? null,
                            'traffic_duration' => isset($element['duration_in_traffic']) ? [
                                'text' => $element['duration_in_traffic']['text'],
                                'value' => $element['duration_in_traffic']['value'],
                            ] : null,
                        ];
                    }
                }
                
                Log::error('Distance calculation failed', [
                    'origin' => $origin,
                    'destination' => $destination,
                    'mode' => $mode,
                    'status' => $data['status'] ?? 'UNKNOWN'
                ]);
                
                // APIが失敗した場合はハヴァサイン公式でフォールバック
                return $this->calculateHaversineDistance($origin, $destination);
                
            } catch (\Exception $e) {
                Log::error('Distance calculation API error', [
                    'origin' => $origin,
                    'destination' => $destination,
                    'error' => $e->getMessage()
                ]);
                
                // エラー時もハヴァサイン公式でフォールバック
                return $this->calculateHaversineDistance($origin, $destination);
            }
        });
    }

    /**
     * ルート最適化（複数地点の最適順序を計算）
     * 
     * @param array $origin ['lat' => float, 'lng' => float]
     * @param array $destinations [['lat' => float, 'lng' => float], ...]
     * @param string $mode
     * @param array $options
     * @return array|null
     */
    public function optimizeRoute(array $origin, array $destinations, string $mode = 'driving', array $options = []): ?array
    {
        if (!$this->isEnabled() || count($destinations) < 2) {
            return $this->calculateSimpleRoute($origin, $destinations);
        }

        $cacheKey = "route_optimization:" . md5(json_encode([$origin, $destinations, $mode, $options]));
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($origin, $destinations, $mode, $options) {
            try {
                // Waypoints（中間地点）として設定
                $waypoints = array_map(function ($dest) {
                    return "{$dest['lat']},{$dest['lng']}";
                }, $destinations);

                $params = [
                    'origin' => "{$origin['lat']},{$origin['lng']}",
                    'destination' => end($waypoints), // 最後の地点を目的地に設定
                    'waypoints' => 'optimize:true|' . implode('|', array_slice($waypoints, 0, -1)),
                    'mode' => $mode,
                    'language' => 'ja',
                    'key' => $this->apiKey,
                ];

                // オプション追加
                if (!empty($options['avoid'])) {
                    $params['avoid'] = implode('|', $options['avoid']);
                }

                $response = Http::get("{$this->baseUrl}/directions/json", $params);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'OK' && !empty($data['routes'])) {
                        $route = $data['routes'][0];
                        
                        return [
                            'optimized_order' => $data['routes'][0]['waypoint_order'] ?? [],
                            'total_distance' => [
                                'text' => $this->formatDistance($route['legs']),
                                'value' => array_sum(array_column($route['legs'], 'distance.value')),
                            ],
                            'total_duration' => [
                                'text' => $this->formatDuration($route['legs']),
                                'value' => array_sum(array_column($route['legs'], 'duration.value')),
                            ],
                            'legs' => $route['legs'],
                            'overview_polyline' => $route['overview_polyline']['points'] ?? null,
                            'bounds' => $route['bounds'] ?? null,
                        ];
                    }
                }
                
                // APIが失敗した場合は簡易計算でフォールバック
                return $this->calculateSimpleRoute($origin, $destinations);
                
            } catch (\Exception $e) {
                Log::error('Route optimization API error', [
                    'origin' => $origin,
                    'destinations' => $destinations,
                    'error' => $e->getMessage()
                ]);
                
                return $this->calculateSimpleRoute($origin, $destinations);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Places API機能
    |--------------------------------------------------------------------------
    */

    /**
     * 近隣の場所を検索
     * 
     * @param float $latitude
     * @param float $longitude
     * @param string $type
     * @param int $radius
     * @param array $options
     * @return array|null
     */
    public function searchNearbyPlaces(float $latitude, float $longitude, string $type = '', int $radius = 1000, array $options = []): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('Google Maps API is disabled');
            return null;
        }

        $cacheKey = "nearby_places:" . md5(json_encode([$latitude, $longitude, $type, $radius, $options]));
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($latitude, $longitude, $type, $radius, $options) {
            try {
                $params = [
                    'location' => "{$latitude},{$longitude}",
                    'radius' => $radius,
                    'language' => 'ja',
                    'key' => $this->apiKey,
                ];

                if (!empty($type)) {
                    $params['type'] = $type;
                }
                if (!empty($options['keyword'])) {
                    $params['keyword'] = $options['keyword'];
                }

                $response = Http::get("{$this->baseUrl}/place/nearbysearch/json", $params);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'OK') {
                        return [
                            'places' => $data['results'],
                            'next_page_token' => $data['next_page_token'] ?? null,
                        ];
                    }
                }
                
                return null;
                
            } catch (\Exception $e) {
                Log::error('Nearby places search API error', [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'error' => $e->getMessage()
                ]);
                
                return null;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ヘルパーメソッド
    |--------------------------------------------------------------------------
    */

    /**
     * ハヴァサイン公式による距離計算（フォールバック用）
     * 
     * @param array $origin
     * @param array $destination
     * @return array
     */
    private function calculateHaversineDistance(array $origin, array $destination): array
    {
        $earthRadius = 6371; // 地球の半径（km）
        
        $dLat = deg2rad($destination['lat'] - $origin['lat']);
        $dLng = deg2rad($destination['lng'] - $origin['lng']);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($origin['lat'])) * cos(deg2rad($destination['lat'])) * 
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        
        // 推定時間計算（平均時速30km）
        $duration = ($distance / 30) * 3600; // 秒
        
        return [
            'distance' => [
                'text' => number_format($distance, 1) . ' km',
                'value' => $distance * 1000, // メートル
            ],
            'duration' => [
                'text' => $this->formatSeconds($duration),
                'value' => (int)$duration,
            ],
            'mode' => 'haversine_calculation',
            'is_estimated' => true,
        ];
    }

    /**
     * 簡易ルート計算（フォールバック用）
     * 
     * @param array $origin
     * @param array $destinations
     * @return array
     */
    private function calculateSimpleRoute(array $origin, array $destinations): array
    {
        $totalDistance = 0;
        $totalDuration = 0;
        $currentLocation = $origin;
        $optimizedOrder = [];
        
        // 単純な最近隣法で順序を決定
        $remainingDestinations = $destinations;
        
        while (!empty($remainingDestinations)) {
            $nearestIndex = 0;
            $nearestDistance = PHP_FLOAT_MAX;
            
            foreach ($remainingDestinations as $index => $destination) {
                $result = $this->calculateHaversineDistance($currentLocation, $destination);
                $distance = $result['distance']['value'];
                
                if ($distance < $nearestDistance) {
                    $nearestDistance = $distance;
                    $nearestIndex = $index;
                }
            }
            
            $nextDestination = $remainingDestinations[$nearestIndex];
            $result = $this->calculateHaversineDistance($currentLocation, $nextDestination);
            
            $totalDistance += $result['distance']['value'];
            $totalDuration += $result['duration']['value'];
            
            $optimizedOrder[] = $nearestIndex;
            $currentLocation = $nextDestination;
            
            unset($remainingDestinations[$nearestIndex]);
            $remainingDestinations = array_values($remainingDestinations);
        }
        
        return [
            'optimized_order' => $optimizedOrder,
            'total_distance' => [
                'text' => number_format($totalDistance / 1000, 1) . ' km',
                'value' => $totalDistance,
            ],
            'total_duration' => [
                'text' => $this->formatSeconds($totalDuration),
                'value' => (int)$totalDuration,
            ],
            'is_estimated' => true,
            'method' => 'simple_nearest_neighbor',
        ];
    }

    /**
     * 距離をフォーマット
     * 
     * @param array $legs
     * @return string
     */
    private function formatDistance(array $legs): string
    {
        $totalMeters = array_sum(array_column($legs, 'distance.value'));
        
        if ($totalMeters >= 1000) {
            return number_format($totalMeters / 1000, 1) . ' km';
        } else {
            return $totalMeters . ' m';
        }
    }

    /**
     * 時間をフォーマット
     * 
     * @param array $legs
     * @return string
     */
    private function formatDuration(array $legs): string
    {
        $totalSeconds = array_sum(array_column($legs, 'duration.value'));
        return $this->formatSeconds($totalSeconds);
    }

    /**
     * 秒数を人間が読みやすい形式にフォーマット
     * 
     * @param int $seconds
     * @return string
     */
    private function formatSeconds(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours > 0) {
            return $hours . '時間' . $minutes . '分';
        } else {
            return $minutes . '分';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 設定・管理機能
    |--------------------------------------------------------------------------
    */

    /**
     * API使用量を取得（概算）
     * 
     * @return array
     */
    public function getApiUsageStats(): array
    {
        // 実際の実装では使用量トラッキングを行う
        return [
            'geocoding_requests' => Cache::get('gmaps_geocoding_count', 0),
            'directions_requests' => Cache::get('gmaps_directions_count', 0),
            'distance_matrix_requests' => Cache::get('gmaps_distance_count', 0),
            'places_requests' => Cache::get('gmaps_places_count', 0),
            'daily_limit' => 25000, // 例: 1日あたりの制限
            'monthly_limit' => 750000, // 例: 1ヶ月あたりの制限
        ];
    }

    /**
     * キャッシュをクリア
     * 
     * @param string|null $type
     * @return bool
     */
    public function clearCache(string $type = null): bool
    {
        $patterns = [
            'geocode' => 'geocode:*',
            'reverse_geocode' => 'reverse_geocode:*',
            'distance' => 'distance:*',
            'route_optimization' => 'route_optimization:*',
            'nearby_places' => 'nearby_places:*',
        ];

        if ($type && isset($patterns[$type])) {
            return Cache::forget($patterns[$type]);
        } else {
            // すべてのキャッシュをクリア
            foreach ($patterns as $pattern) {
                Cache::forget($pattern);
            }
            return true;
        }
    }

    /**
     * API接続テスト
     * 
     * @return array
     */
    public function testConnection(): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Google Maps API is not enabled',
                'details' => 'API key is missing or API is disabled in configuration'
            ];
        }

        try {
            // 簡単なジオコーディングテスト
            $result = $this->geocodeAddress('東京駅');
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Google Maps API connection successful',
                    'details' => 'Test geocoding request completed successfully',
                    'test_result' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Google Maps API connection failed',
                    'details' => 'Test geocoding request returned no results'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Google Maps API connection error',
                'details' => $e->getMessage()
            ];
        }
    }
}
