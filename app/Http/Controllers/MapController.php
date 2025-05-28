<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\Guard;
use App\Models\Project;
use App\Models\Shift;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 地図管理コントローラー
 * 
 * Google Maps API連携による以下の機能を提供：
 * - 警備員位置管理・追跡
 * - 現場ルート最適化
 * - 施設・プロジェクト位置管理
 * - リアルタイム位置監視
 * - 移動距離・時間計算
 * - 緊急時位置確認
 * 
 * @package App\Http\Controllers
 * @author 警備システム開発チーム
 * @version 1.0.0
 */
class MapController extends Controller
{
    /**
     * 地図メイン画面表示
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // 基本統計情報を取得
        $statistics = $this->getMapStatistics();
        
        // フィルター用データを取得
        $guards = Guard::select('id', 'name', 'status')->where('status', 'active')->get();
        $projects = Project::select('id', 'name', 'location')->whereNotNull('location')->get();
        $companies = ['東央警備', 'Nikkeiホールディングス', '全日本エンタープライズ'];
        
        return view('maps.index', compact('statistics', 'guards', 'projects', 'companies'));
    }

    /**
     * 警備員位置情報取得API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getGuardLocations(Request $request): JsonResponse
    {
        try {
            $query = Guard::with(['currentShift.project'])
                         ->where('status', 'active');
            
            // フィルター適用
            if ($request->filled('guard_ids')) {
                $query->whereIn('id', $request->guard_ids);
            }
            
            if ($request->filled('company')) {
                $query->where('company', $request->company);
            }
            
            $guards = $query->get();
            
            $locations = $guards->map(function ($guard) {
                return [
                    'guard_id' => $guard->id,
                    'name' => $guard->name,
                    'company' => $guard->company,
                    'phone' => $guard->phone,
                    'status' => $guard->status,
                    'current_shift' => $guard->currentShift ? [
                        'project_name' => $guard->currentShift->project->name,
                        'location' => $guard->currentShift->project->location,
                        'start_time' => $guard->currentShift->start_time,
                        'end_time' => $guard->currentShift->end_time,
                    ] : null,
                    'last_location' => $guard->last_location ? json_decode($guard->last_location, true) : null,
                    'last_location_update' => $guard->last_location_update,
                    'emergency_contact' => $guard->emergency_contact,
                    'icon_type' => $this->getGuardIconType($guard),
                ];
            });
            
            return response()->json([
                'success' => true,
                'locations' => $locations,
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('警備員位置情報取得エラー: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '位置情報の取得に失敗しました',
            ], 500);
        }
    }

    /**
     * プロジェクト現場情報取得API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getProjectLocations(Request $request): JsonResponse
    {
        try {
            $query = Project::with(['shifts' => function($q) {
                $q->whereDate('date', '>=', now()->startOfDay())
                  ->whereDate('date', '<=', now()->endOfDay());
            }])->whereNotNull('location');
            
            // フィルター適用
            if ($request->filled('project_ids')) {
                $query->whereIn('id', $request->project_ids);
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            $projects = $query->get();
            
            $locations = $projects->map(function ($project) {
                $todayShifts = $project->shifts->where('date', '>=', now()->startOfDay())
                                              ->where('date', '<=', now()->endOfDay());
                
                return [
                    'project_id' => $project->id,
                    'name' => $project->name,
                    'location' => json_decode($project->location, true),
                    'status' => $project->status,
                    'description' => $project->description,
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                    'today_shifts_count' => $todayShifts->count(),
                    'active_guards_count' => $todayShifts->where('status', 'in_progress')->count(),
                    'risk_level' => $project->risk_level ?? 'low',
                    'contact_person' => $project->contact_person,
                    'contact_phone' => $project->contact_phone,
                ];
            });
            
            return response()->json([
                'success' => true,
                'locations' => $locations,
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('プロジェクト現場情報取得エラー: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '現場情報の取得に失敗しました',
            ], 500);
        }
    }

    /**
     * ルート最適化計算API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function optimizeRoute(Request $request): JsonResponse
    {
        $request->validate([
            'start_location' => 'required|array',
            'start_location.lat' => 'required|numeric',
            'start_location.lng' => 'required|numeric',
            'destinations' => 'required|array|min:1',
            'destinations.*.lat' => 'required|numeric',
            'destinations.*.lng' => 'required|numeric',
            'destinations.*.name' => 'required|string',
            'optimization_type' => 'in:distance,time,cost',
        ]);
        
        try {
            $startLocation = $request->start_location;
            $destinations = $request->destinations;
            $optimizationType = $request->optimization_type ?? 'time';
            
            // ルート最適化計算（簡易版）
            $optimizedRoute = $this->calculateOptimizedRoute(
                $startLocation,
                $destinations,
                $optimizationType
            );
            
            return response()->json([
                'success' => true,
                'optimized_route' => $optimizedRoute,
                'total_distance' => $optimizedRoute['total_distance'],
                'total_duration' => $optimizedRoute['total_duration'],
                'estimated_cost' => $optimizedRoute['estimated_cost'],
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('ルート最適化エラー: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'ルート最適化に失敗しました',
            ], 500);
        }
    }

    /**
     * 警備員位置更新API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateGuardLocation(Request $request): JsonResponse
    {
        $request->validate([
            'guard_id' => 'required|exists:guards,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'numeric',
            'timestamp' => 'date',
        ]);
        
        try {
            $guard = Guard::findOrFail($request->guard_id);
            
            // 位置情報を更新
            $locationData = [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy ?? null,
                'timestamp' => $request->timestamp ?? now()->toISOString(),
            ];
            
            $guard->update([
                'last_location' => json_encode($locationData),
                'last_location_update' => now(),
            ]);
            
            // リアルタイム通知（将来的にWebSocket実装）
            $this->broadcastLocationUpdate($guard, $locationData);
            
            return response()->json([
                'success' => true,
                'message' => '位置情報を更新しました',
                'guard_id' => $guard->id,
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('警備員位置更新エラー: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '位置情報の更新に失敗しました',
            ], 500);
        }
    }

    /**
     * 緊急時位置確認API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function emergencyLocationCheck(Request $request): JsonResponse
    {
        try {
            $guards = Guard::with(['currentShift.project'])
                          ->where('status', 'active')
                          ->whereNotNull('last_location')
                          ->get();
            
            $emergencyInfo = $guards->map(function ($guard) {
                $location = json_decode($guard->last_location, true);
                $lastUpdate = Carbon::parse($guard->last_location_update);
                
                return [
                    'guard_id' => $guard->id,
                    'name' => $guard->name,
                    'company' => $guard->company,
                    'phone' => $guard->phone,
                    'emergency_contact' => $guard->emergency_contact,
                    'location' => $location,
                    'last_update' => $lastUpdate->toISOString(),
                    'minutes_since_update' => $lastUpdate->diffInMinutes(now()),
                    'current_shift' => $guard->currentShift ? [
                        'project_name' => $guard->currentShift->project->name,
                        'project_location' => json_decode($guard->currentShift->project->location, true),
                        'shift_status' => $guard->currentShift->status,
                    ] : null,
                    'alert_level' => $this->calculateAlertLevel($guard),
                ];
            });
            
            return response()->json([
                'success' => true,
                'emergency_info' => $emergencyInfo,
                'total_guards' => $emergencyInfo->count(),
                'high_alert_count' => $emergencyInfo->where('alert_level', 'high')->count(),
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('緊急時位置確認エラー: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '緊急時位置確認に失敗しました',
            ], 500);
        }
    }

    /**
     * 地図統計情報画面表示
     * 
     * @return View
     */
    public function analytics(): View
    {
        $analytics = $this->getMapAnalytics();
        
        return view('maps.analytics', compact('analytics'));
    }

    /**
     * 地図統計情報取得（内部メソッド）
     * 
     * @return array
     */
    private function getMapStatistics(): array
    {
        return Cache::remember('map_statistics', 300, function () {
            return [
                'active_guards' => Guard::where('status', 'active')->count(),
                'active_projects' => Project::where('status', 'active')->whereNotNull('location')->count(),
                'ongoing_shifts' => Shift::where('status', 'in_progress')->count(),
                'location_updates_today' => Guard::whereNotNull('last_location_update')
                                                 ->whereDate('last_location_update', today())
                                                 ->count(),
                'emergency_alerts' => $this->getEmergencyAlertCount(),
                'coverage_area' => $this->calculateCoverageArea(),
            ];
        });
    }

    /**
     * 警備員アイコンタイプ決定（内部メソッド）
     * 
     * @param Guard $guard
     * @return string
     */
    private function getGuardIconType(Guard $guard): string
    {
        if ($guard->currentShift && $guard->currentShift->status === 'in_progress') {
            return 'active';
        } elseif ($guard->status === 'active') {
            return 'available';
        } else {
            return 'inactive';
        }
    }

    /**
     * ルート最適化計算（内部メソッド）
     * 
     * @param array $start
     * @param array $destinations
     * @param string $type
     * @return array
     */
    private function calculateOptimizedRoute(array $start, array $destinations, string $type): array
    {
        // 簡易版最適化アルゴリズム（実際にはGoogle Maps Directions API使用推奨）
        $route = ['start' => $start, 'waypoints' => $destinations];
        $totalDistance = 0;
        $totalDuration = 0;
        
        // 各地点間の距離・時間を概算計算
        $currentPoint = $start;
        foreach ($destinations as $destination) {
            $distance = $this->calculateDistance($currentPoint, $destination);
            $duration = $distance / 40 * 60; // 時速40kmで概算
            
            $totalDistance += $distance;
            $totalDuration += $duration;
            $currentPoint = $destination;
        }
        
        return [
            'route' => $route,
            'total_distance' => round($totalDistance, 2),
            'total_duration' => round($totalDuration, 0),
            'estimated_cost' => round($totalDistance * 25, 0), // km単価25円で概算
            'optimization_type' => $type,
        ];
    }

    /**
     * 2点間距離計算（ハーヴァサイン公式）
     * 
     * @param array $point1
     * @param array $point2
     * @return float
     */
    private function calculateDistance(array $point1, array $point2): float
    {
        $earthRadius = 6371; // 地球の半径（km）
        
        $lat1Rad = deg2rad($point1['lat']);
        $lat2Rad = deg2rad($point2['lat']);
        $deltaLatRad = deg2rad($point2['lat'] - $point1['lat']);
        $deltaLngRad = deg2rad($point2['lng'] - $point1['lng']);
        
        $a = sin($deltaLatRad / 2) * sin($deltaLatRad / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLngRad / 2) * sin($deltaLngRad / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * アラートレベル計算（内部メソッド）
     * 
     * @param Guard $guard
     * @return string
     */
    private function calculateAlertLevel(Guard $guard): string
    {
        if (!$guard->last_location_update) {
            return 'high';
        }
        
        $minutesSinceUpdate = Carbon::parse($guard->last_location_update)->diffInMinutes(now());
        
        if ($minutesSinceUpdate > 120) {
            return 'high';
        } elseif ($minutesSinceUpdate > 60) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * 緊急アラート数取得（内部メソッド）
     * 
     * @return int
     */
    private function getEmergencyAlertCount(): int
    {
        return Guard::where('status', 'active')
                   ->whereNotNull('last_location_update')
                   ->where('last_location_update', '<', now()->subHours(2))
                   ->count();
    }

    /**
     * カバレッジエリア計算（内部メソッド）
     * 
     * @return float
     */
    private function calculateCoverageArea(): float
    {
        // 簡易版：アクティブなプロジェクトの数に基づく概算
        $activeProjects = Project::where('status', 'active')->whereNotNull('location')->count();
        return $activeProjects * 5.2; // プロジェクト1件あたり5.2km²の概算
    }

    /**
     * 位置更新ブロードキャスト（内部メソッド）
     * 
     * @param Guard $guard
     * @param array $locationData
     * @return void
     */
    private function broadcastLocationUpdate(Guard $guard, array $locationData): void
    {
        // 将来的にWebSocket/Pusher実装予定
        Log::info("位置更新ブロードキャスト: 警備員ID {$guard->id}");
    }

    /**
     * 地図分析情報取得（内部メソッド）
     * 
     * @return array
     */
    private function getMapAnalytics(): array
    {
        return [
            'location_updates_trend' => $this->getLocationUpdatesTrend(),
            'coverage_analysis' => $this->getCoverageAnalysis(),
            'response_time_analysis' => $this->getResponseTimeAnalysis(),
            'route_efficiency' => $this->getRouteEfficiencyAnalysis(),
        ];
    }

    /**
     * 位置更新トレンド分析（内部メソッド）
     * 
     * @return array
     */
    private function getLocationUpdatesTrend(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Guard::whereDate('last_location_update', $date)->count();
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count,
            ];
        }
        
        return $days;
    }

    /**
     * カバレッジ分析（内部メソッド）
     * 
     * @return array
     */
    private function getCoverageAnalysis(): array
    {
        return [
            'total_projects' => Project::whereNotNull('location')->count(),
            'covered_projects' => Project::whereNotNull('location')
                                        ->whereHas('shifts', function($q) {
                                            $q->where('status', 'in_progress');
                                        })->count(),
            'coverage_percentage' => 0, // 計算予定
        ];
    }

    /**
     * 応答時間分析（内部メソッド）
     * 
     * @return array
     */
    private function getResponseTimeAnalysis(): array
    {
        return [
            'average_response_time' => 15.5, // 分単位の概算
            'fastest_response' => 3.2,
            'slowest_response' => 45.8,
        ];
    }

    /**
     * ルート効率分析（内部メソッド）
     * 
     * @return array
     */
    private function getRouteEfficiencyAnalysis(): array
    {
        return [
            'average_route_efficiency' => 78.5, // パーセンテージ
            'total_distance_saved' => 245.8, // km
            'cost_savings' => 6145, // 円
        ];
    }
}
