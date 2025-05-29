<?php

namespace App\Services;

use App\Models\Weather;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * 天気予報サービス
 * 
 * OpenWeatherMap APIとの連携を管理
 * 天気データの取得、保存、キャッシュ機能を提供
 */
class WeatherService
{
    /**
     * APIキー
     */
    private string $apiKey;

    /**
     * ベースURL
     */
    private string $baseUrl;

    /**
     * デフォルト単位系
     */
    private string $units;

    /**
     * デフォルト言語
     */
    private string $lang;

    /**
     * キャッシュ期間（秒）
     */
    private int $cacheDuration;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->apiKey = config('services.weather.api_key', env('WEATHER_API_KEY'));
        $this->baseUrl = config('services.weather.base_url', env('WEATHER_API_BASE_URL'));
        $this->units = config('services.weather.units', env('WEATHER_DEFAULT_UNITS', 'metric'));
        $this->lang = config('services.weather.lang', env('WEATHER_DEFAULT_LANG', 'ja'));
        $this->cacheDuration = config('services.weather.cache_duration', env('WEATHER_CACHE_DURATION', 1800));
    }

    /**
     * 現在の天気情報取得
     */
    public function getCurrentWeather(string $locationName, float $latitude, float $longitude): ?Weather
    {
        try {
            $cacheKey = "weather_current_{$latitude}_{$longitude}";
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($locationName, $latitude, $longitude) {
                $response = Http::get($this->baseUrl . '/weather', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'appid' => $this->apiKey,
                    'units' => $this->units,
                    'lang' => $this->lang,
                ]);

                if (!$response->successful()) {
                    Log::error('Weather API Error', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                    return null;
                }

                $data = $response->json();
                return $this->saveWeatherData($locationName, $latitude, $longitude, $data, 'current');
            });
        } catch (\Exception $e) {
            Log::error('Weather Service Error', [
                'message' => $e->getMessage(),
                'location' => $locationName,
                'coordinates' => [$latitude, $longitude],
            ]);
            return null;
        }
    }

    /**
     * 5日間天気予報取得
     */
    public function getForecastWeather(string $locationName, float $latitude, float $longitude): array
    {
        try {
            $cacheKey = "weather_forecast_{$latitude}_{$longitude}";
            
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($locationName, $latitude, $longitude) {
                $response = Http::get($this->baseUrl . '/forecast', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'appid' => $this->apiKey,
                    'units' => $this->units,
                    'lang' => $this->lang,
                ]);

                if (!$response->successful()) {
                    Log::error('Weather Forecast API Error', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                    return [];
                }

                $data = $response->json();
                $forecasts = [];

                foreach ($data['list'] as $forecastData) {
                    $weather = $this->saveWeatherData(
                        $locationName, 
                        $latitude, 
                        $longitude, 
                        $forecastData, 
                        'forecast'
                    );
                    
                    if ($weather) {
                        $forecasts[] = $weather;
                    }
                }

                return $forecasts;
            });
        } catch (\Exception $e) {
            Log::error('Weather Forecast Service Error', [
                'message' => $e->getMessage(),
                'location' => $locationName,
                'coordinates' => [$latitude, $longitude],
            ]);
            return [];
        }
    }

    /**
     * 複数地点の天気情報一括取得
     */
    public function getMultipleLocationWeather(array $locations): array
    {
        $results = [];
        
        foreach ($locations as $location) {
            $current = $this->getCurrentWeather(
                $location['name'],
                $location['latitude'],
                $location['longitude']
            );
            
            if ($current) {
                $results[$location['name']] = [
                    'current' => $current,
                    'forecast' => $this->getForecastWeather(
                        $location['name'],
                        $location['latitude'],
                        $location['longitude']
                    ),
                ];
            }
        }
        
        return $results;
    }

    /**
     * 天気データ保存
     */
    private function saveWeatherData(
        string $locationName, 
        float $latitude, 
        float $longitude, 
        array $data, 
        string $dataType
    ): ?Weather {
        try {
            $weatherDate = isset($data['dt']) 
                ? Carbon::createFromTimestamp($data['dt'])
                : Carbon::now();

            $weatherData = [
                'location_name' => $locationName,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'weather_date' => $weatherDate,
                'weather_main' => $data['weather'][0]['main'] ?? '',
                'weather_description' => $data['weather'][0]['description'] ?? '',
                'weather_icon' => $data['weather'][0]['icon'] ?? '',
                'temperature' => $data['main']['temp'] ?? 0,
                'feels_like' => $data['main']['feels_like'] ?? 0,
                'temp_min' => $data['main']['temp_min'] ?? 0,
                'temp_max' => $data['main']['temp_max'] ?? 0,
                'humidity' => $data['main']['humidity'] ?? 0,
                'pressure' => $data['main']['pressure'] ?? 0,
                'sea_level' => $data['main']['sea_level'] ?? null,
                'ground_level' => $data['main']['grnd_level'] ?? null,
                'wind_speed' => $data['wind']['speed'] ?? null,
                'wind_deg' => $data['wind']['deg'] ?? null,
                'wind_gust' => $data['wind']['gust'] ?? null,
                'rain_1h' => $data['rain']['1h'] ?? null,
                'rain_3h' => $data['rain']['3h'] ?? null,
                'snow_1h' => $data['snow']['1h'] ?? null,
                'snow_3h' => $data['snow']['3h'] ?? null,
                'clouds' => $data['clouds']['all'] ?? 0,
                'visibility' => $data['visibility'] ?? null,
                'uv_index' => null, // 別途UV Index APIが必要
                'weather_risk_level' => $this->calculateRiskLevel($data),
                'weather_alerts' => $this->extractAlerts($data),
                'outdoor_work_suitable' => $this->assessOutdoorWorkSuitability($data),
                'data_type' => $dataType,
                'api_source' => 'openweather',
                'api_fetched_at' => Carbon::now(),
                'raw_data' => $data,
            ];

            // 既存データがある場合は更新、なければ新規作成
            $existingWeather = Weather::where('location_name', $locationName)
                ->where('weather_date', $weatherDate)
                ->where('data_type', $dataType)
                ->first();

            if ($existingWeather) {
                $existingWeather->update($weatherData);
                return $existingWeather;
            } else {
                return Weather::create($weatherData);
            }
        } catch (\Exception $e) {
            Log::error('Weather Data Save Error', [
                'message' => $e->getMessage(),
                'location' => $locationName,
                'data' => $data,
            ]);
            return null;
        }
    }

    /**
     * リスクレベル計算
     */
    private function calculateRiskLevel(array $data): string
    {
        $riskScore = 0;
        
        // 気温によるリスク
        $temp = $data['main']['temp'] ?? 0;
        if ($temp < -5 || $temp > 35) {
            $riskScore += 3;
        } elseif ($temp < 0 || $temp > 30) {
            $riskScore += 2;
        }

        // 降水量によるリスク
        $rain1h = $data['rain']['1h'] ?? 0;
        if ($rain1h > 20) {
            $riskScore += 3;
        } elseif ($rain1h > 10) {
            $riskScore += 2;
        } elseif ($rain1h > 5) {
            $riskScore += 1;
        }

        // 風速によるリスク
        $windSpeed = $data['wind']['speed'] ?? 0;
        if ($windSpeed > 15) {
            $riskScore += 3;
        } elseif ($windSpeed > 10) {
            $riskScore += 2;
        } elseif ($windSpeed > 7) {
            $riskScore += 1;
        }

        // 視程によるリスク
        $visibility = $data['visibility'] ?? 10000;
        if ($visibility < 1000) {
            $riskScore += 3;
        } elseif ($visibility < 5000) {
            $riskScore += 1;
        }

        // リスクレベル判定
        if ($riskScore >= 7) {
            return 'critical';
        } elseif ($riskScore >= 4) {
            return 'high';
        } elseif ($riskScore >= 2) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * 気象警報・注意報抽出
     */
    private function extractAlerts(array $data): ?array
    {
        $alerts = [];

        // 気温アラート
        $temp = $data['main']['temp'] ?? 0;
        if ($temp < -5) {
            $alerts[] = ['type' => 'cold', 'message' => '極低温注意'];
        } elseif ($temp > 35) {
            $alerts[] = ['type' => 'heat', 'message' => '猛暑注意'];
        }

        // 降水アラート
        $rain1h = $data['rain']['1h'] ?? 0;
        if ($rain1h > 20) {
            $alerts[] = ['type' => 'heavy_rain', 'message' => '大雨警報'];
        } elseif ($rain1h > 10) {
            $alerts[] = ['type' => 'rain', 'message' => '雨注意報'];
        }

        // 強風アラート
        $windSpeed = $data['wind']['speed'] ?? 0;
        if ($windSpeed > 15) {
            $alerts[] = ['type' => 'strong_wind', 'message' => '強風警報'];
        }

        // 視界不良アラート
        $visibility = $data['visibility'] ?? 10000;
        if ($visibility < 1000) {
            $alerts[] = ['type' => 'poor_visibility', 'message' => '視界不良警報'];
        }

        return empty($alerts) ? null : $alerts;
    }

    /**
     * 屋外作業適性評価
     */
    private function assessOutdoorWorkSuitability(array $data): bool
    {
        // 気温チェック
        $temp = $data['main']['temp'] ?? 0;
        if ($temp < -10 || $temp > 40) {
            return false;
        }

        // 降水量チェック
        $rain1h = $data['rain']['1h'] ?? 0;
        if ($rain1h > 15) {
            return false;
        }

        // 風速チェック
        $windSpeed = $data['wind']['speed'] ?? 0;
        if ($windSpeed > 20) {
            return false;
        }

        // 視程チェック
        $visibility = $data['visibility'] ?? 10000;
        if ($visibility < 500) {
            return false;
        }

        // 雷雨チェック
        $weatherMain = $data['weather'][0]['main'] ?? '';
        if ($weatherMain === 'Thunderstorm') {
            return false;
        }

        return true;
    }

    /**
     * 警備地点の天気情報更新
     */
    public function updateSecurityLocationWeather(): array
    {
        // プロジェクトから警備地点を取得
        $projects = \App\Models\Project::where('status', 'active')
            ->whereNotNull('location_address')
            ->get();

        $results = [];
        
        foreach ($projects as $project) {
            // 住所から座標を取得（Google Geocoding API使用）
            $coordinates = $this->getCoordinatesFromAddress($project->location_address);
            
            if ($coordinates) {
                $weather = $this->getCurrentWeather(
                    $project->location_address,
                    $coordinates['lat'],
                    $coordinates['lng']
                );
                
                if ($weather) {
                    $results[$project->id] = $weather;
                }
            }
        }
        
        return $results;
    }

    /**
     * 住所から座標取得（Google Geocoding API使用）
     */
    private function getCoordinatesFromAddress(string $address): ?array
    {
        try {
            $apiKey = env('GOOGLE_MAPS_API_KEY');
            if (!$apiKey) {
                return null;
            }

            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['results'][0]['geometry']['location'])) {
                    return [
                        'lat' => $data['results'][0]['geometry']['location']['lat'],
                        'lng' => $data['results'][0]['geometry']['location']['lng'],
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Geocoding Error', [
                'message' => $e->getMessage(),
                'address' => $address,
            ]);
        }
        
        return null;
    }

    /**
     * 天気情報クリーンアップ（古いデータ削除）
     */
    public function cleanupOldWeatherData(int $daysToKeep = 30): int
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        return Weather::where('weather_date', '<', $cutoffDate)->delete();
    }
}
