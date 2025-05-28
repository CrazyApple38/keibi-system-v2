<?php

namespace App\Services;

use App\Models\Guard;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * 位置情報管理サービス
 * 
 * 警備員・プロジェクトの位置情報管理、履歴管理、監視機能を提供
 * Google Maps APIと連携した高度な位置情報処理
 */
class LocationService
{
    /**
     * Google Maps サービス
     */
    private GoogleMapsService $googleMapsService;

    /**
     * 位置情報の有効期限（分）
     */
    private int $locationValidityMinutes = 30;

    /**
     * 位置履歴保持期間（日）
     */
    private int $historyRetentionDays = 30;

    /**
     * コンストラクタ
     */
    public function __construct(GoogleMapsService $googleMapsService)
    {
        $this->googleMapsService = $googleMapsService;
    }

    /*
    |--------------------------------------------------------------------------
    | 警備員位置情報管理
    |--------------------------------------------------------------------------
    */

    /**
     * 警備員の位置情報を更新
     * 
     * @param int $guardId
     * @param float $latitude
     * @param float $longitude
     * @param array $options
     * @return array
     */
    public function updateGuardLocation(int $guardId, float $latitude, float $longitude, array $options = []): array
    {
        try {
            $guard = Guard::findOrFail($guardId);

            // 住所を逆ジオコーディングで取得
            $address = null;
            if ($this->googleMapsService->isEnabled()) {
                $reverseGeocode = $this->googleMapsService->reverseGeocode($latitude, $longitude);
                $address = $reverseGeocode['formatted_address'] ?? null;
            }

            // 位置情報を更新
            $guard->update([
                'location_lat' => $latitude,
                'location_lng' => $longitude,
                'location_accuracy' => $options['accuracy'] ?? null,
                'location_address' => $address,
                'location_updated_at' => now(),
            ]);

            // 位置履歴を更新
            $this->addLocationToHistory($guard);

            // 現場エリア監視
            $areaStatus = $this->checkGuardInAuthorizedAreas($guard);

            Log::info('Guard location updated', [
                'guard_id' => $guardId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'address' => $address,
            ]);

            return [
                'success' => true,
                'guard_id' => $guardId,
                'location' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'accuracy' => $options['accuracy'] ?? null,
                    'address' => $address,
                    'updated_at' => now()->toISOString(),
                ],
                'area_status' => $areaStatus,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to update guard location', [
                'guard_id' => $guardId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 警備員の位置履歴を取得
     * 
     * @param int $guardId
     * @param array $options
     * @return array
     */
    public function getGuardLocationHistory(int $guardId, array $options = []): array
    {
        $guard = Guard::findOrFail($guardId);
        
        $dateFrom = isset($options['date_from']) ? 
            Carbon::parse($options['date_from']) : 
            Carbon::now()->subDays(7);
        
        $dateTo = isset($options['date_to']) ? 
            Carbon::parse($options['date_to']) : 
            Carbon::now();

        // 位置履歴を取得（実際の実装では専用テーブルを使用することを推奨）
        $history = $guard->location_history ?? [];
        
        // 日付範囲でフィルタリング
        $filteredHistory = array_filter($history, function($point) use ($dateFrom, $dateTo) {
            $pointTime = Carbon::parse($point['timestamp']);
            return $pointTime->between($dateFrom, $dateTo);
        });

        // 統計情報を計算
        $stats = $this->calculateLocationStats($filteredHistory);

        return [
            'guard' => [
                'id' => $guard->id,
                'name' => $guard->user->name ?? '',
                'employee_id' => $guard->employee_id,
            ],
            'history' => array_values($filteredHistory),
            'period' => [
                'from' => $dateFrom->format('Y-m-d H:i:s'),
                'to' => $dateTo->format('Y-m-d H:i:s'),
            ],
            'statistics' => $stats,
        ];
    }

    /**
     * アクティブな警備員の位置情報を取得
     * 
     * @param array $options
     * @return array
     */
    public function getActiveGuardLocations(array $options = []): array
    {
        $companyId = $options['company_id'] ?? null;
        $includeInactive = $options['include_inactive'] ?? false;
        
        $query = Guard::with(['user', 'company'])
            ->whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->where('location_sharing_enabled', true);

        if (!$includeInactive) {
            $query->where('status', 'active');
        }

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        // 位置情報の新しさでフィルタリング
        if (isset($options['max_age_minutes'])) {
            $cutoffTime = Carbon::now()->subMinutes($options['max_age_minutes']);
            $query->where('location_updated_at', '>=', $cutoffTime);
        }

        $guards = $query->get();

        return $guards->map(function($guard) {
            return [
                'id' => $guard->id,
                'name' => $guard->user->name ?? '',
                'employee_id' => $guard->employee_id,
                'company' => $guard->company->name ?? '',
                'location' => [
                    'latitude' => $guard->location_lat,
                    'longitude' => $guard->location_lng,
                    'accuracy' => $guard->location_accuracy,
                    'address' => $guard->location_address,
                    'updated_at' => $guard->location_updated_at?->toISOString(),
                ],
                'status' => $guard->status,
                'current_shift' => $guard->getCurrentShift()?->only(['id', 'project.name', 'start_time', 'end_time']),
                'is_valid_location' => $guard->hasValidLocation($this->locationValidityMinutes),
            ];
        })->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | プロジェクト位置情報管理
    |--------------------------------------------------------------------------
    */

    /**
     * プロジェクトの位置情報を更新
     * 
     * @param int $projectId
     * @param array $locationData
     * @return array
     */
    public function updateProjectLocation(int $projectId, array $locationData): array
    {
        try {
            $project = Project::findOrFail($projectId);

            // 住所から座標を取得（必要に応じて）
            if (empty($locationData['latitude']) && !empty($locationData['address'])) {
                if ($this->googleMapsService->isEnabled()) {
                    $geocode = $this->googleMapsService->geocodeAddress($locationData['address']);
                    if ($geocode) {
                        $locationData['latitude'] = $geocode['latitude'];
                        $locationData['longitude'] = $geocode['longitude'];
                    }
                }
            }

            $project->update([
                'location_lat' => $locationData['latitude'] ?? $project->location_lat,
                'location_lng' => $locationData['longitude'] ?? $project->location_lng,
                'location_address' => $locationData['address'] ?? $project->location_address,
                'location_building' => $locationData['building'] ?? $project->location_building,
                'location_floor' => $locationData['floor'] ?? $project->location_floor,
                'location_room' => $locationData['room'] ?? $project->location_room,
                'location_notes' => $locationData['notes'] ?? $project->location_notes,
                'location_radius' => $locationData['radius'] ?? $project->location_radius ?? 100,
                'parking_info' => $locationData['parking_info'] ?? $project->parking_info ?? [],
                'access_info' => $locationData['access_info'] ?? $project->access_info ?? [],
            ]);

            Log::info('Project location updated', [
                'project_id' => $projectId,
                'location' => $locationData,
            ]);

            return [
                'success' => true,
                'project_id' => $projectId,
                'location' => $project->map_info,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to update project location', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * プロジェクトの位置情報を取得
     * 
     * @param array $options
     * @return array
     */
    public function getProjectLocations(array $options = []): array
    {
        $companyId = $options['company_id'] ?? null;
        $status = $options['status'] ?? 'active';
        
        $query = Project::with(['customer'])
            ->whereNotNull('location_lat')
            ->whereNotNull('location_lng');

        if ($status) {
            $query->where('status', $status);
        }

        if ($companyId) {
            $query->where('customer_id', $companyId);
        }

        $projects = $query->get();

        return $projects->map(function($project) {
            return $project->map_info;
        })->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | 距離・ルート計算
    |--------------------------------------------------------------------------
    */

    /**
     * 2点間の距離を計算
     * 
     * @param array $origin
     * @param array $destination
     * @param array $options
     * @return array
     */
    public function calculateDistance(array $origin, array $destination, array $options = []): array
    {
        $mode = $options['mode'] ?? 'driving';
        $useApi = $options['use_api'] ?? true;

        if ($useApi && $this->googleMapsService->isEnabled()) {
            return $this->googleMapsService->calculateDistance($origin, $destination, $mode, $options);
        } else {
            // ハヴァサイン公式でフォールバック
            return $this->calculateHaversineDistance($origin, $destination);
        }
    }

    /**
     * 最適ルートを計算
     * 
     * @param array $origin
     * @param array $destinations
     * @param array $options
     * @return array
     */
    public function calculateOptimizedRoute(array $origin, array $destinations, array $options = []): array
    {
        $mode = $options['mode'] ?? 'driving';
        $useApi = $options['use_api'] ?? true;

        if ($useApi && $this->googleMapsService->isEnabled()) {
            return $this->googleMapsService->optimizeRoute($origin, $destinations, $mode, $options);
        } else {
            // 簡易計算でフォールバック
            return $this->calculateSimpleOptimizedRoute($origin, $destinations);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | エリア監視・警告機能
    |--------------------------------------------------------------------------
    */

    /**
     * 警備員が許可エリア内にいるかチェック
     * 
     * @param Guard $guard
     * @return array
     */
    public function checkGuardInAuthorizedAreas(Guard $guard): array
    {
        if (!$guard->hasLocation()) {
            return [
                'status' => 'no_location',
                'message' => '位置情報が取得できません',
                'areas' => [],
            ];
        }

        $currentShift = $guard->getCurrentShift();
        $authorizedAreas = [];
        $violations = [];

        if ($currentShift && $currentShift->project->hasLocation()) {
            $project = $currentShift->project;
            $isInRange = $project->isGuardInRange($guard);
            $distance = $project->getDistanceFromGuard($guard);

            $areaInfo = [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'authorized_radius' => $project->location_radius,
                'distance_from_center' => $distance,
                'is_authorized' => $isInRange,
            ];

            $authorizedAreas[] = $areaInfo;

            if (!$isInRange) {
                $violations[] = [
                    'type' => 'outside_authorized_area',
                    'project' => $project->name,
                    'distance' => $distance,
                    'allowed_radius' => $project->location_radius,
                ];
            }
        }

        $status = empty($violations) ? 'authorized' : 'violation';

        return [
            'status' => $status,
            'message' => $this->getAreaStatusMessage($status, $violations),
            'areas' => $authorizedAreas,
            'violations' => $violations,
            'checked_at' => now()->toISOString(),
        ];
    }

    /**
     * 緊急時の最寄り警備員を検索
     * 
     * @param float $latitude
     * @param float $longitude
     * @param array $options
     * @return array
     */
    public function findNearestGuardsForEmergency(float $latitude, float $longitude, array $options = []): array
    {
        $maxDistance = $options['max_distance'] ?? 10; // km
        $maxGuards = $options['max_guards'] ?? 5;
        $onlyAvailable = $options['only_available'] ?? true;

        $query = Guard::where('status', 'active')
            ->whereNotNull('location_lat')
            ->whereNotNull('location_lng')
            ->where('location_sharing_enabled', true);

        if ($onlyAvailable) {
            // 現在シフトに入っていない警備員のみ
            $query->whereDoesntHave('shifts', function($q) {
                $now = now();
                $q->where('shift_date', $now->format('Y-m-d'))
                  ->where('start_time', '<=', $now->format('H:i:s'))
                  ->where('end_time', '>=', $now->format('H:i:s'))
                  ->where('status', 'active');
            });
        }

        $guards = $query->with(['user'])->get();

        // 距離を計算してフィルタリング
        $nearbyGuards = $guards->map(function($guard) use ($latitude, $longitude) {
            $distance = $this->calculateHaversineDistance(
                ['lat' => $latitude, 'lng' => $longitude],
                ['lat' => $guard->location_lat, 'lng' => $guard->location_lng]
            );

            return [
                'guard' => $guard,
                'distance' => $distance['distance']['value'] / 1000, // km
                'eta' => $distance['duration']['value'] / 60, // 分
            ];
        })->filter(function($item) use ($maxDistance) {
            return $item['distance'] <= $maxDistance;
        })->sortBy('distance')->take($maxGuards);

        return [
            'emergency_location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'nearest_guards' => $nearbyGuards->map(function($item) {
                return [
                    'id' => $item['guard']->id,
                    'name' => $item['guard']->user->name ?? '',
                    'employee_id' => $item['guard']->employee_id,
                    'phone' => $item['guard']->phone_number,
                    'location' => [
                        'latitude' => $item['guard']->location_lat,
                        'longitude' => $item['guard']->location_lng,
                        'address' => $item['guard']->location_address,
                    ],
                    'distance_km' => round($item['distance'], 2),
                    'estimated_arrival_minutes' => round($item['eta']),
                    'status' => $item['guard']->status,
                ];
            })->values(),
            'search_radius' => $maxDistance,
            'total_found' => $nearbyGuards->count(),
            'searched_at' => now()->toISOString(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | プライベートヘルパーメソッド
    |--------------------------------------------------------------------------
    */

    /**
     * 警備員の位置履歴に追加
     * 
     * @param Guard $guard
     * @return void
     */
    private function addLocationToHistory(Guard $guard): void
    {
        if (!$guard->hasLocation()) {
            return;
        }

        $history = $guard->location_history ?? [];
        
        // 新しい位置情報を追加
        $newPoint = [
            'lat' => $guard->location_lat,
            'lng' => $guard->location_lng,
            'accuracy' => $guard->location_accuracy,
            'address' => $guard->location_address,
            'timestamp' => now()->toISOString(),
        ];

        array_unshift($history, $newPoint);

        // 保持期間を超えたデータを削除
        $cutoffTime = Carbon::now()->subDays($this->historyRetentionDays);
        $history = array_filter($history, function($point) use ($cutoffTime) {
            return Carbon::parse($point['timestamp'])->gte($cutoffTime);
        });

        // 最大保持件数制限
        if (count($history) > 1000) {
            $history = array_slice($history, 0, 1000);
        }

        $guard->update(['location_history' => $history]);
    }

    /**
     * 位置統計を計算
     * 
     * @param array $history
     * @return array
     */
    private function calculateLocationStats(array $history): array
    {
        if (empty($history)) {
            return [
                'total_points' => 0,
                'total_distance' => 0,
                'average_speed' => 0,
                'max_speed' => 0,
            ];
        }

        $totalDistance = 0;
        $speeds = [];
        
        for ($i = 1; $i < count($history); $i++) {
            $prev = $history[$i - 1];
            $curr = $history[$i];
            
            $distance = $this->calculateHaversineDistance(
                ['lat' => $prev['lat'], 'lng' => $prev['lng']],
                ['lat' => $curr['lat'], 'lng' => $curr['lng']]
            );
            
            $totalDistance += $distance['distance']['value'];
            
            // 速度計算
            $timeDiff = Carbon::parse($prev['timestamp'])->diffInSeconds($curr['timestamp']);
            if ($timeDiff > 0) {
                $speed = ($distance['distance']['value'] / $timeDiff) * 3.6; // km/h
                $speeds[] = $speed;
            }
        }

        return [
            'total_points' => count($history),
            'total_distance' => round($totalDistance / 1000, 2), // km
            'average_speed' => !empty($speeds) ? round(array_sum($speeds) / count($speeds), 2) : 0,
            'max_speed' => !empty($speeds) ? round(max($speeds), 2) : 0,
        ];
    }

    /**
     * ハヴァサイン距離計算
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
        
        $duration = ($distance / 30) * 3600; // 秒（平均時速30km）
        
        return [
            'distance' => [
                'text' => number_format($distance, 1) . ' km',
                'value' => $distance * 1000, // メートル
            ],
            'duration' => [
                'text' => $this->formatSeconds($duration),
                'value' => (int)$duration,
            ],
        ];
    }

    /**
     * 簡易最適化ルート計算
     * 
     * @param array $origin
     * @param array $destinations
     * @return array
     */
    private function calculateSimpleOptimizedRoute(array $origin, array $destinations): array
    {
        // 最近隣法による簡易最適化
        $totalDistance = 0;
        $totalDuration = 0;
        $currentLocation = $origin;
        $optimizedOrder = [];
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
            'method' => 'nearest_neighbor_fallback',
        ];
    }

    /**
     * エリアステータスメッセージを取得
     * 
     * @param string $status
     * @param array $violations
     * @return string
     */
    private function getAreaStatusMessage(string $status, array $violations): string
    {
        switch ($status) {
            case 'authorized':
                return '許可エリア内にいます';
            case 'violation':
                $count = count($violations);
                return "警告: {$count}件のエリア違反が検出されました";
            case 'no_location':
                return '位置情報が取得できません';
            default:
                return '位置情報ステータス不明';
        }
    }

    /**
     * 秒数をフォーマット
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
}
